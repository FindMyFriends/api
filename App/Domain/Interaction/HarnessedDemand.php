<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Misc;
use Klapuch\Output;

/**
 * Demand harnessed by callback
 */
final class HarnessedDemand implements Demand {
	/** @var \FindMyFriends\Domain\Interaction\Demand */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(Demand $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function retract(): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function reconsider(array $description): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
