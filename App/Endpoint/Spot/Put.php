<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Spot;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Storage;
use Klapuch\Validation;

final class Put implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/put.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Application\Request $request,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->request = $request;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		(new Place\ChainedSpot(
			new Place\HarnessedSpot(
				new Place\ExistingSpot(
					new Place\FakeSpot(),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Place\HarnessedSpot(
				new Place\OwnedSpot(
					new Place\FakeSpot(),
					$parameters['id'],
					$this->seeker,
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Place\StoredSpot($parameters['id'], $this->database)
		))->move(
			(new Validation\ChainedRule(
				new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
				new Constraint\SpotRule()
			))->apply((new Internal\DecodedJson($this->request->body()->serialization()))->values())
		);
		return new Response\EmptyResponse();
	}
}
