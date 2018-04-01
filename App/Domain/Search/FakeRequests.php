<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

/**
 * Fake
 */
final class FakeRequests implements Requests {
	public function refresh(int $watching, string $status, ?int $self = null): int {
	}

	public function all(Dataset\Selection $selection): \Iterator {
	}
}