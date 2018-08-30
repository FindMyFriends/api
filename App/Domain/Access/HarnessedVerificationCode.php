<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use FindMyFriends\Misc;
use Klapuch\Output;

/**
 * Verification code harnessed by callback
 */
final class HarnessedVerificationCode implements VerificationCode {
	/** @var \FindMyFriends\Domain\Access\VerificationCode */
	private $origin;

	/** @var \FindMyFriends\Misc\Callback */
	private $callback;

	public function __construct(VerificationCode $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function use(): void {
		$this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}

	public function print(Output\Format $format): Output\Format {
		return $this->callback->invoke([$this->origin, __FUNCTION__], func_get_args());
	}
}
