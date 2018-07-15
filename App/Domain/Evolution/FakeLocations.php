<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

/**
 * Fake
 */
final class FakeLocations implements Locations {
	public function track(array $location): void {
	}

	public function history(): \Iterator {
		return new \ArrayIterator([]);
	}
}
