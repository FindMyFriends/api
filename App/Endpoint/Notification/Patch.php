<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Notification;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Activity;
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
		$notification = new Activity\StoredNotification(
			$parameters['id'],
			$this->connection
		);
		$input = (new Constraint\StructuredJson(
			new \SplFileInfo(self::SCHEMA)
		))->apply(
			(new Internal\DecodedJson(
				$this->request->body()->serialization()
			))->values()
		);
		if (isset($input['seen']))
			if ($input['seen'] === true)
				$notification->seen();
			else
				$notification->unseen();
		return new Response\EmptyResponse();
	}
}
