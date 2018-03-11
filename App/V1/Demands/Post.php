<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Domain\Search;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;
use PhpAmqpLib;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $hashids;
	private $request;
	private $url;
	private $database;
	private $rabbitMq;
	private $user;

	public function __construct(
		HashidsInterface $hashids,
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Access\User $user
	) {
		$this->hashids = $hashids;
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->rabbitMq = $rabbitMq;
		$this->user = $user;
	}

	public function template(array $parameters): Output\Template {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/demands/{id}'),
				[
					'id' => $this->hashids->encode(
						(new Domain\QueuedDemands(
							new Domain\IndividualDemands(
								$this->user,
								$this->database
							),
							new Search\Publisher($this->rabbitMq),
							$this->database
						))->ask(
							(new Validation\ChainedRule(
								new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
								new Constraint\DemandRule()
							))->apply(json_decode($this->request->body()->serialization(), true))
						)
					),
				]
			);
			return new Application\RawTemplate(
				new Response\ConcurrentlyCreatedResponse(
					new Response\EmptyResponse(),
					new Http\PostgresETag($this->database, $url),
					$url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}