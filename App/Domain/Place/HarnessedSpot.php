<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use FindMyFriends\Misc;
use Klapuch\Output;

/**
 * Spot harnessed by callback
 */
final class HarnessedSpot implements Spot {
	/** @var \FindMyFriends\Domain\Place\Spot */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(Spot $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function forget(): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function print(Output\Format $format): Output\Format {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function move(array $movement): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
