<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Storage;

final class Patch implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/patch.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Application\Request $request,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->request = $request;
		$this->connection = $connection;
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
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Interaction\HarnessedDemand(
				new Interaction\OwnedDemand(
					new Interaction\FakeDemand(),
					$parameters['id'],
					$this->seeker,
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Interaction\StoredDemand($parameters['id'], $this->connection)
		))->reconsider(
			(new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)))->apply(
				(new Internal\DecodedJson(
					$this->request->body()->serialization()
				))->values()
			)
		);
		return new Response\EmptyResponse();
	}
}
