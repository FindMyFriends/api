<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Misc;

/**
 * Locations harnessed by callback
 */
final class HarnessedLocations implements Locations {
	/** @var \FindMyFriends\Domain\Evolution\Locations */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(Locations $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function track(array $location): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function history(): \Iterator {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
