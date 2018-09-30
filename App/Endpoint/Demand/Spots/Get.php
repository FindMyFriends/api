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

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $spotHashids,
		HashidsInterface $demandHashids,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->spotHashids = $spotHashids;
		$this->demandHashids = $demandHashids;
		$this->connection = $connection;
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
							(new Interaction\PublicSpots(
								new Place\HarnessedSpots(
									new Interaction\OwnedSpots(
										new Interaction\DemandSpots(
											$parameters['id'],
											$this->connection
										),
										$this->seeker,
										$parameters['id'],
										$this->connection
									),
									new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
								),
								$this->spotHashids,
								$this->demandHashids
							))->history()
						)
					)
				)
			),
			$parameters
		);
	}
}
