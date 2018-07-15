<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution\Locations;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
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
	 * @param array $parameters
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Application\Response
	 */
	public function response(array $parameters): Application\Response {
		(new Evolution\ChainedLocation(
			new Evolution\HarnessedLocation(
				new Evolution\OwnedLocation(
					new Evolution\FakeLocation(),
					$parameters['id'],
					$this->seeker,
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Evolution\StoredLocation($parameters['id'], $this->database)
		))->forget();
		return new Response\EmptyResponse();
	}
}
