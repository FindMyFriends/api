<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Misc;
use Klapuch\Output;

/**
 * Change harnessed by callback
 */
final class HarnessedChange implements Change {
	private $origin;
	private $callback;

	public function __construct(Change $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function affect(array $changes): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function print(Output\Format $format): Output\Format {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function revert(): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}