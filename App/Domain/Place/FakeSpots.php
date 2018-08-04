<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

/**
 * Fake
 */
final class FakeSpots implements Locations {
	public function track(array $location): void {
	}

	public function history(): \Iterator {
		return new \ArrayIterator([]);
	}
}
