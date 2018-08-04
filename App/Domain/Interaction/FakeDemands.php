<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use Klapuch\Dataset;

final class FakeDemands implements Demands {
	public function ask(array $description): int {
	}

	public function all(Dataset\Selection $selection): \Iterator {
	}

	public function count(Dataset\Selection $selection): int {
	}
}
