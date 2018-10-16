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
use Klapuch\Validation;

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
		$soulmate = new Search\ChainedSoulmate(
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
		);
		$input = (new Validation\ChainedRule(
			new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
			new Constraint\SoulmateRule()
		))->apply(
			(new Internal\DecodedJson(
				$this->request->body()->serialization()
			))->values()
		);
		if (isset($input['is_correct']))
			$soulmate->clarify($input['is_correct']);
		if (isset($input['is_exposed']) && $input['is_exposed'])
			$soulmate->expose();
		return new Response\EmptyResponse();
	}
}
