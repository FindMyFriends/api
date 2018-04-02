<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

/**
 * Fake
 */
final class FakePublisher implements Publisher {
	public function publish(int $demand): void {
	}
}