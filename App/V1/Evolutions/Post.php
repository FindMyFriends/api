<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Evolutions;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Predis;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $request;
	private $url;
	private $database;
	private $user;
	private $redis;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		\PDO $database,
		Access\User $user,
		Predis\ClientInterface $redis
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
		$this->redis = $redis;
	}

	public function template(array $parameters): Output\Template {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/evolutions/{id}'),
				(new Domain\IndividualEvolutions(
					$this->user,
					$this->database
				))->evolve(
					(new Constraint\StructuredJson(
						new \SplFileInfo(self::SCHEMA)
					))->apply(json_decode($this->request->body()->serialization(), true))
				)
			);
			return new Application\RawTemplate(
				new Response\ConcurrentlyCreatedResponse(
					new Response\JsonResponse(new Response\EmptyResponse()),
					new Http\ETagRedis($this->redis),
					$url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}