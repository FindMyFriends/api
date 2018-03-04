<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Soulmates formatted to be used for input/output
 */
final class FormattedSoulmates implements Soulmates {
	private $origin;
	private $hashids;

	public function __construct(Soulmates $origin, array $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function find(int $demand): void {
		$this->origin->find($demand);
	}

	public function matches(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->matches($selection),
			function(Soulmate $soulmate): Soulmate {
				return new FormattedSoulmate($soulmate, $this->hashids);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}