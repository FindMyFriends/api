<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;

final class FakeVerificationCode implements VerificationCode {
	private $format;

	public function __construct(?Output\Format $format = null) {
		$this->format = $format;
	}

	public function use(): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $this->format;
	}
}
