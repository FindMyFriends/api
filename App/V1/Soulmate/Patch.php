<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Soulmate;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
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
			(new Search\ChainedSoulmate(
				new Search\HarnessedSoulmate(
					new Search\ExistingSoulmate(
						new Search\FakeSoulmate(),
						$parameters['id'],
						$this->database
					),
					new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
				),
				new Search\HarnessedSoulmate(
					new Search\OwnedSoulmate(
						new Search\FakeSoulmate(),
						$parameters['id'],
						$this->seeker,
						$this->database
					),
					new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
				),
				new Search\StoredSoulmate($parameters['id'], $this->database)
			))->clarify(
				(new Constraint\StructuredJson(
					new \SplFileInfo(self::SCHEMA)
				))->apply(
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
