<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

use Klapuch\Log;

final class LoggedJob implements Job {
	/** @var \FindMyFriends\Scheduling\Job */
	private $origin;

	/** @var \Klapuch\Log\Logs */
	private $logs;

	public function __construct(Job $origin, Log\Logs $logs) {
		$this->origin = $origin;
		$this->logs = $logs;
	}

	public function fulfill(): void {
		try {
			$this->origin->fulfill();
		} catch (\Throwable $e) {
			$this->logs->put($e, new Log\CurrentEnvironment());
			throw $e;
		}
	}

	public function name(): string {
		return $this->origin->name();
	}
}
