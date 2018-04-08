<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Misc;

/**
 * Publisher harnessed by callback
 */
final class HarnessedPublisher implements Publisher {
	private $origin;
	private $callback;

	public function __construct(Publisher $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function publish(int $demand): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
