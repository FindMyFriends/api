<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Evolutions formatted to be used for input/output
 */
final class FormattedEvolutions implements Evolutions {
	private $origin;

	public function __construct(Evolutions $origin) {
		$this->origin = $origin;
	}

	public function evolve(array $progress): Evolution {
		return $this->origin->evolve($progress);
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->changes($selection),
			function(Evolution $demand): Evolution {
				return new FormattedEvolution($demand);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}