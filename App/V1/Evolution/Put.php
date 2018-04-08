<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Evolution;

use Elasticsearch;
use FindMyFriends\Constraint;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Request;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;

final class Put implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/put.json';
	private $request;
	private $url;
	private $database;
	private $elasticsearch;
	private $seeker;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch,
		Access\User $seeker
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		try {
			(new Evolution\SyncChange(
				$parameters['id'],
				new Evolution\ChainedChange(
					new Evolution\HarnessedChange(
						new Evolution\ExistingChange(
							new Evolution\FakeChange(),
							$parameters['id'],
							$this->database
						),
						new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
					),
					new Evolution\HarnessedChange(
						new Evolution\PermittedChange(
							new Evolution\FakeChange(),
							$parameters['id'],
							$this->seeker,
							$this->database
						),
						new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
					),
					new Evolution\StoredChange($parameters['id'], $this->database)
				),
				$this->elasticsearch
			))->affect(
				(new Validation\ChainedRule(
					new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
					new Constraint\EvolutionRule()
				))->apply(
					json_decode(
						(new Request\ConcurrentlyControlledRequest(
							$this->request,
							new Http\PostgresETag($this->database, $this->url)
						))->body()->serialization(),
						true
					)
				)
			);
			return new Response\EmptyResponse();
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
