<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution\Spots;

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
	private $spotHashids;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $spotHashids,
		HashidsInterface $evolutionHashids,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->spotHashids = $spotHashids;
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
		return new Response\PartialResponse(
			new Response\JsonResponse(
				new Response\PlainResponse(
					new Misc\JsonPrintedObjects(
						...iterator_to_array(
							(new Evolution\PublicSpots(
								new Place\HarnessedSpots(
									new Evolution\OwnedSpots(
										new Evolution\ChangeSpots(
											$parameters['id'],
											$this->database
										),
										$this->seeker,
										$parameters['id'],
										$this->database
									),
									new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
								),
								$this->spotHashids,
								$this->evolutionHashids
							))->history()
						)
					)
				)
			),
			$parameters
		);
	}
}
