<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeSpot implements Spot {
	public function forget(): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	public function move(array $movement): void {
	}
}
