<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use FindMyFriends\Misc;
use Klapuch\Output;

/**
 * Evolution harnessed by callback
 */
final class HarnessedEvolution implements Evolution {
	private $origin;
	private $callback;

	public function __construct(Evolution $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function change(array $changes): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function print(Output\Format $format): Output\Format {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}