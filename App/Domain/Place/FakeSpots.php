<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

/**
 * Fake
 */
final class FakeSpots implements Spots {
	public function track(array $spot): void {
	}

	public function history(): \Iterator {
		return new \ArrayIterator([]);
	}
}
