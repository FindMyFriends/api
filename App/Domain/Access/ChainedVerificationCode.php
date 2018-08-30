<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;

/**
 * Verification code called sequentially one by one behaving as a single one
 */
final class ChainedVerificationCode implements VerificationCode {
	/** @var \FindMyFriends\Domain\Access\VerificationCode[] */
	private $origins;

	public function __construct(VerificationCode ...$origins) {
		$this->origins = $origins;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function use(): void {
		foreach ($this->origins as $origin)
			$origin->use();
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}
