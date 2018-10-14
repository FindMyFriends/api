<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use FindMyFriends;
use Klapuch\Output;
use Klapuch\Storage;

final class StoredNotification implements Notification {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(int $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	public function receive(Output\Format $format): Output\Format {
		$notification = (new Storage\TypedQuery(
			$this->connection,
			(new FindMyFriends\Sql\IndividualNotifications\Select())
				->from(['notifications'])
				->where('id = ?')
				->sql(),
			[$this->id]
		))->row();
		return new Output\FilledFormat($format, $notification);
	}
}
