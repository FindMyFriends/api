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
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Storage\Connection $connection,
		Elasticsearch\Client $elasticsearch,
		Access\Seeker $seeker
	) {
		$this->connection = $connection;
		$this->elasticsearch = $elasticsearch;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		(new Evolution\SyncChange(
			$parameters['id'],
			new Evolution\ChainedChange(
				new Evolution\HarnessedChange(
					new Evolution\ExistingChange(
						new Evolution\FakeChange(),
						$parameters['id'],
						$this->connection
					),
					new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
				),
				new Evolution\HarnessedChange(
					new Evolution\OwnedChange(
						new Evolution\FakeChange(),
						$parameters['id'],
						$this->seeker,
						$this->connection
					),
					new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
				),
				new Evolution\StoredChange($parameters['id'], $this->connection)
			),
			$this->elasticsearch
		))->revert();
		return new Response\EmptyResponse();
	}
}
