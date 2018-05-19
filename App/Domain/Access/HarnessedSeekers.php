<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use FindMyFriends\Misc;

/**
 * Seekers harnessed by callback
 */
final class HarnessedSeekers implements Seekers {
	private $origin;
	private $callback;

	public function __construct(Seekers $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function join(array $credentials): Seeker {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
