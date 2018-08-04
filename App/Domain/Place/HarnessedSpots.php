<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use FindMyFriends\Misc;

/**
 * Spots harnessed by callback
 */
final class HarnessedSpots implements Spots {
	/** @var \FindMyFriends\Domain\Place\Spots */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(Spots $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function track(array $spot): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function history(): \Iterator {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
