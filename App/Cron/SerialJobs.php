<?php
declare(strict_types = 1);

namespace FindMyFriends\Cron;

final class SerialJobs implements Job {
	/** @var \FindMyFriends\Cron\Job[] */
	private $origins;

	public function __construct(Job ...$origins) {
		$this->origins = $origins;
	}

	public function fulfill(): void {
		foreach ($this->origins as $origin)
			$origin->fulfill();
	}

	public function name(): string {
		return '';
	}
}
