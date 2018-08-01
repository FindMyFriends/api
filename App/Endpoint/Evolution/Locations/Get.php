<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution\Locations;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Storage;

final class Get implements Application\View {
	/** @var \Hashids\HashidsInterface */
	private $locationHashids;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $locationHashids,
		HashidsInterface $evolutionHashids,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->locationHashids = $locationHashids;
		$this->evolutionHashids = $evolutionHashids;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	/**
	 * @param array $parameters
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Application\Response
	 */
	public function response(array $parameters): Application\Response {
		$locations = new Evolution\PublicLocations(
			new Place\HarnessedLocations(
				new Evolution\OwnedLocations(
					new Evolution\ChangeLocations($parameters['id'], $this->database),
					$this->seeker,
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			$this->locationHashids,
			$this->evolutionHashids
		);
		return new Response\PlainResponse(
			new Misc\JsonPrintedObjects(
				...iterator_to_array($locations->history())
			)
		);
	}
}
