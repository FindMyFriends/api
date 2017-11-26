<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Evolutions;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $request;
	private $url;
	private $database;
	private $user;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		\PDO $database,
		Access\User $user
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
	}

	public function template(array $parameters): Output\Template {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/evolutions/{id}'),
				(new Evolution\IndividualChain(
					$this->user,
					$this->database
				))->extend(
					(new Validation\ChainedRule(
						new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
						new Constraint\EvolutionRule()
					))->apply(json_decode($this->request->body()->serialization(), true))
				)
			);
			return new Application\RawTemplate(
				new Response\ConcurrentlyCreatedResponse(
					new Response\JsonResponse(new Response\EmptyResponse()),
					new Http\PostgresETag($this->database, $this->url),
					$url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}