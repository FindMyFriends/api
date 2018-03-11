<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Hashids\HashidsInterface;
use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Demand formatted to be used for public representation
 */
final class PublicDemands implements Demands {
	private $origin;
	private $hashids;

	public function __construct(Demands $origin, HashidsInterface $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function ask(array $description): int {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->all($selection),
			function(Demand $demand): Demand {
				return new PublicDemand($demand, $this->hashids);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}