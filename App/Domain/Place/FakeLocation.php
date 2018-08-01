<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeLocation implements Location {
	public function forget(): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}
