<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution;

use Elasticsearch;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Storage;

final class Delete implements Application\View {
	private $database;
	private $elasticsearch;
	private $seeker;

	public function __construct(
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch,
		Access\Seeker $seeker
	) {
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
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
		))->revert();
		return new Response\EmptyResponse();
	}
}