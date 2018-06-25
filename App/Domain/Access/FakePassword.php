<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;

/**
 * Fake
 */
final class FakePassword implements Password {
	/** @var \Klapuch\Output\Format|null */
	private $print;

	public function __construct(?Output\Format $print = null) {
		$this->print = $print;
	}

	public function change(string $password): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $this->print;
	}
}
