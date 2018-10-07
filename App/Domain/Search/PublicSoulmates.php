<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Soulmates formatted to be used for public representation
 */
final class PublicSoulmates implements Soulmates {
	/** @var \FindMyFriends\Domain\Search\Soulmates */
	private $origin;

	/** @var mixed[] */
	private $hashids;

	public function __construct(Soulmates $origin, array $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	/**
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function matches(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->matches($selection),
			function(Soulmate $soulmate): Soulmate {
				return new PublicSoulmate($soulmate, $this->hashids);
			}
		);
	}

	/**
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}
