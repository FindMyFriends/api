<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $hashids;
	private $request;
	private $url;
	private $database;
	private $user;

	public function __construct(
		HashidsInterface $hashids,
		Application\Request $request,
		Uri\Uri $url,
		\PDO $database,
		Access\User $user
	) {
		$this->hashids = $hashids;
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
	}

	public function template(array $parameters): Output\Template {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/demands/{id}'),
				new Domain\FormattedDemand(
					(new Domain\IndividualDemands(
						$this->user,
						$this->database
					))->ask(
						(new Validation\ChainedRule(
							new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
							new Constraint\DemandRule()
						))->apply(json_decode($this->request->body()->serialization(), true))
					),
					$this->hashids
				)
			);
			return new Application\RawTemplate(
				new Response\ConcurrentlyCreatedResponse(
					new Response\JsonResponse(new Response\EmptyResponse()),
					new Http\PostgresETag($this->database, $url),
					$url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}