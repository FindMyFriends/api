<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Evolution;

use Elasticsearch;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;

final class Delete implements Application\View {
	private $database;
	private $elasticsearch;
	private $seeker;

	public function __construct(
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch,
		Access\User $seeker
	) {
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
		$this->seeker = $seeker;
	}

	public function template(array $parameters): Output\Template {
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
			))->revert();
			return new Application\RawTemplate(new Response\EmptyResponse());
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}