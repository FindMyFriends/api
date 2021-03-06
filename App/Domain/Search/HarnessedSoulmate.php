<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Misc;
use Klapuch\Output;

/**
 * Soulmate harnessed by callback
 */
final class HarnessedSoulmate implements Soulmate {
	/** @var \FindMyFriends\Domain\Search\Soulmate */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(Soulmate $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function clarify(bool $correct): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function expose(): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
