<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use FindMyFriends;
use FindMyFriends\Domain\Access;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Notifications belonging to the seeker
 */
final class IndividualNotifications implements Notifications {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Access\Seeker $seeker, Storage\Connection $connection) {
		$this->seeker = $seeker;
		$this->connection = $connection;
	}

	public function receive(Dataset\Selection $selection): \Iterator {
		$notifications = (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new FindMyFriends\Sql\IndividualNotifications\Select())
					->from(['notifications'])
					->where('seeker_id = :seeker_id', ['seeker_id' => $this->seeker->id()]),
				$selection
			)
		))->rows();
		foreach ($notifications as $notification) {
			yield new StoredNotification(
				$notification['id'],
				new Storage\MemoryConnection($this->connection, $notification)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new Sql\AnsiSelect(['count(*)']))
					->from(['notifications'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->field();
	}
}
