<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;

/**
 * Entrance harnessed by callback
 */
final class HarnessedEntrance implements Access\Entrance {
	private $origin;
	private $callback;

	public function __construct(Access\Entrance $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function enter(array $credentials): Access\Seeker {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function exit(): Access\Seeker {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
