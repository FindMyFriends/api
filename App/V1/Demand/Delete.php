<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;

final class Delete implements Application\View {
	private $database;
	private $seeker;

	public function __construct(\PDO $database, Access\Seeker $seeker) {
		$this->database = $database;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		try {
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
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
