<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

use Klapuch\Storage;

final class RepeatedJob implements Job {
	/** @var \FindMyFriends\Scheduling\Job */
	private $origin;

	/** @var string */
	private $interval;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Job $origin, string $interval, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->interval = $interval;
		$this->connection = $connection;
	}

	public function fulfill(): void {
		if ($this->ready($this->interval))
			$this->origin->fulfill();
	}

	private function ready(string $interval): bool {
		return (bool) (new Storage\TypedQuery(
			$this->connection,
			"SELECT NOT EXISTS (
				SELECT 1
				FROM log.cron_jobs
				WHERE name = :name
			) OR EXISTS (
				SELECT 1
				FROM log.cron_jobs
				WHERE name = :name
				AND status = 'succeed'
				GROUP BY name
				HAVING MAX(marked_at) + :interval <= now()
			)",
			['name' => $this->name(), 'interval' => $interval]
		))->field();
	}

	public function name(): string {
		return $this->origin->name();
	}
}
