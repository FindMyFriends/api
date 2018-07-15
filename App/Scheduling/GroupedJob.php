<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

final class GroupedJob implements Job {
	/** @var string */
	private $name;

	/** @var \FindMyFriends\Scheduling\Job */
	private $origin;

	public function __construct(string $name, Job $origin) {
		$this->name = $name;
		$this->origin = $origin;
	}

	public function fulfill(): void {
		$this->origin->fulfill();
	}

	public function name(): string {
		return $this->name;
	}
}
