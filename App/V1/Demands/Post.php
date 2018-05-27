<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
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
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->rabbitMq = $rabbitMq;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/demands/{id}'),
				[
					'id' => $this->hashids->encode(
						(new Domain\QueuedDemands(
							new Domain\IndividualDemands(
								$this->seeker,
								$this->database
							),
							new Search\AmqpPublisher($this->rabbitMq)
						))->ask(
							(new Validation\ChainedRule(
								new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
								new Constraint\DemandRule()
							))->apply(json_decode($this->request->body()->serialization(), true))
						)
					),
				]
			);
			return new Response\ConcurrentlyCreatedResponse(
				new Response\CreatedResponse(new Response\EmptyResponse(), $url),
				new Http\PostgresETag($this->database, $url)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
