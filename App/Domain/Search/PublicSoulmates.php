<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Soulmates formatted to be used for public representation
 */
final class PublicSoulmates implements Soulmates {
	private $origin;
	private $hashids;

	public function __construct(Soulmates $origin, array $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function find(): void {
		$this->origin->find();
	}

	public function matches(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->matches($selection),
			function(Soulmate $soulmate): Soulmate {
				return new PublicSoulmate($soulmate, $this->hashids);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}