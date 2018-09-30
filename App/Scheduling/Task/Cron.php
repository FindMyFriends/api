<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use Klapuch\Storage;

final class Cron implements Scheduling\Job {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function fulfill(): void {
		(new Scheduling\SerialJobs(
			new Scheduling\RepeatedJob(
				new Scheduling\MarkedJob(
					new RefreshMaterializedView($this->connection),
					$this->connection
				),
				'PT10M',
				$this->connection
			)
		))->fulfill();
	}

	public function name(): string {
		return 'Cron';
	}
}
