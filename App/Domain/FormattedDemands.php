<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Demands formatted to be used for input/output
 */
final class FormattedDemands implements Demands {
	private $origin;

	public function __construct(Demands $origin) {
		$this->origin = $origin;
	}

	public function ask(array $description): Demand {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		return new Iterator\MappedIterator(
			$this->origin->all($selection),
			function(Demand $demand): Demand {
				return new FormattedDemand($demand);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}