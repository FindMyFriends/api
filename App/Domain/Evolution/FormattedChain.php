<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Hashids\HashidsInterface;
use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Chain formatted to be used for input/output
 */
final class FormattedChain implements Chain {
	private $origin;
	private $hashids;

	public function __construct(Chain $origin, HashidsInterface $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function extend(array $progress): Change {
		return $this->origin->extend($progress);
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->changes($selection),
			function(Change $demand): Change {
				return new FormattedChange($demand, $this->hashids);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}