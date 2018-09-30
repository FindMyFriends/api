<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Soulmate;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
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
		(new Search\ChainedSoulmate(
			new Search\HarnessedSoulmate(
				new Search\ExistingSoulmate(
					new Search\FakeSoulmate(),
					$parameters['id'],
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Search\HarnessedSoulmate(
				new Search\OwnedSoulmate(
					new Search\FakeSoulmate(),
					$parameters['id'],
					$this->seeker,
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Search\StoredSoulmate($parameters['id'], $this->connection)
		))->clarify(
			(new Constraint\StructuredJson(
				new \SplFileInfo(self::SCHEMA)
			))->apply(
				(new Internal\DecodedJson(
					$this->request->body()->serialization()
				))->values()
			)
		);
		return new Response\EmptyResponse();
	}
}
