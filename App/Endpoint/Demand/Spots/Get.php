<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Storage;

final class Get implements Application\View {
	/** @var \Hashids\HashidsInterface */
	private $spotHashids;

	/** @var \Hashids\HashidsInterface */
	private $demandHashids;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $spotHashids,
		HashidsInterface $demandHashids,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->spotHashids = $spotHashids;
		$this->demandHashids = $demandHashids;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	/**
	 * @param array $parameters
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Application\Response
	 */
	public function response(array $parameters): Application\Response {
		$spots = new Interaction\PublicSpots(
			new Place\HarnessedSpots(
				new Interaction\OwnedSpots(
					new Interaction\DemandSpots($parameters['id'], $this->database),
					$this->seeker,
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			$this->spotHashids,
			$this->demandHashids
		);
		return new Response\PlainResponse(
			new Misc\JsonPrintedObjects(
				...iterator_to_array($spots->history())
			)
		);
	}
}
