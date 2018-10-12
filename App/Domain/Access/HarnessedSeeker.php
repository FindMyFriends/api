<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use FindMyFriends\Misc;

/**
 * Seeker harnessed by callback
 */
final class HarnessedSeeker implements Seeker {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(Seeker $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function id(): string {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function properties(): array {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
