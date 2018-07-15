<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

final class FakeJob implements Job {
	/** @var callable|null */
	private $action;

	/** @var string|null */
	private $name;

	public function __construct(?callable $action = null, ?string $name = null) {
		$this->action = $action;
		$this->name = $name;
	}

	public function fulfill(): void {
		if ($this->action !== null)
			call_user_func($this->action);
	}

	public function name(): string {
		return $this->name;
	}
}
