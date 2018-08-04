<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
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
		(new Interaction\ChainedDemand(
			new Interaction\HarnessedDemand(
				new Interaction\ExistingDemand(
					new Interaction\FakeDemand(),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Interaction\HarnessedDemand(
				new Interaction\OwnedDemand(
					new Interaction\FakeDemand(),
					$parameters['id'],
					$this->seeker,
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Interaction\StoredDemand($parameters['id'], $this->database)
		))->retract();
		return new Response\EmptyResponse();
	}
}
