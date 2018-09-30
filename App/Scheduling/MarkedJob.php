<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

use Klapuch\Storage;

final class MarkedJob implements Job {
	/** @var \FindMyFriends\Scheduling\Job */
	private $origin;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Job $origin, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->connection = $connection;
	}

	public function fulfill(): void {
		$id = $this->mark('processing');
		try {
			$this->origin->fulfill();
			$this->mark('succeed', $id);
		} catch (\Throwable $e) {
			$this->mark('failed', $id);
			throw $e;
		}
	}

	private function mark(string $status, ?int $self = null): int {
		return (new Storage\TypedQuery(
			$this->connection,
			'INSERT INTO log.cron_jobs(status, name, self_id) VALUES (?, ?, ?)
			RETURNING COALESCE(self_id, id)',
			[$status, $this->name(), $self]
		))->field();
	}

	public function name(): string {
		return $this->origin->name();
	}
}
