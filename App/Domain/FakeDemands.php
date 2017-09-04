<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;

final class FakeDemands implements Demands {
	public function ask(array $description): Demand {
	}

	public function all(Dataset\Selection $selection): \Traversable {
	}
}