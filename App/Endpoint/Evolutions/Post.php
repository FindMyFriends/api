<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolutions;

use Elasticsearch;
use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $hashids;
	private $request;
	private $url;
	private $database;
	private $elasticsearch;
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'evolutions/{id}'),
				[
					'id' => $this->hashids->encode(
						(new Evolution\SyncChain(
							new Evolution\IndividualChain(
								$this->seeker,
								$this->database
							),
							$this->elasticsearch
						))->extend(
							(new Validation\ChainedRule(
								new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
								new Constraint\EvolutionRule()
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
