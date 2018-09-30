<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Storage;

final class Delete implements Application\View {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(Storage\Connection $connection, Access\Seeker $seeker) {
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	/**
	 * @param array $parameters
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Application\Response
	 */
	public function response(array $parameters): Application\Response {
		(new Place\ChainedSpot(
			new Place\HarnessedSpot(
				new Place\OwnedSpot(
					new Place\FakeSpot(),
					$parameters['id'],
					$this->seeker,
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Evolution\StoredSpot($parameters['id'], $this->connection)
		))->forget();
		return new Response\EmptyResponse();
	}
}
