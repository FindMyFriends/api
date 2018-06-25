<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Storage;

final class Delete implements Application\View {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(Storage\MetaPDO $database, Access\Seeker $seeker) {
		$this->database = $database;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		(new Domain\ChainedDemand(
			new Domain\HarnessedDemand(
				new Domain\ExistingDemand(
					new Domain\FakeDemand(),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Domain\HarnessedDemand(
				new Domain\OwnedDemand(
					new Domain\FakeDemand(),
					$parameters['id'],
					$this->seeker,
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Domain\StoredDemand($parameters['id'], $this->database)
		))->retract();
		return new Response\EmptyResponse();
	}
}
