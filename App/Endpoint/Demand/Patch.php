<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Storage;

final class Patch implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/patch.json';
	private $request;
	private $database;
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
			))->reconsider(
				(new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)))->apply(
					json_decode(
						$this->request->body()->serialization(),
						true
					)
				)
			);
			return new Response\EmptyResponse();
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
