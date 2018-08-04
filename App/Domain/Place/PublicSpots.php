<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Hashids\HashidsInterface;
use Klapuch\Iterator;

/**
 * Spots formatted to be used for public representation
 */
final class PublicSpots implements Spots {
	/** @var \FindMyFriends\Domain\Place\Spots */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $spotHashids;

	public function __construct(Spots $origin, HashidsInterface $spotHashids) {
		$this->origin = $origin;
		$this->spotHashids = $spotHashids;
	}

	/**
	 * @param mixed[] $spot
	 * @throws \UnexpectedValueException
	 */
	public function track(array $spot): void {
		$this->origin->track($spot);
	}

	/**
	 * @return \Iterator
	 * @throws \UnexpectedValueException
	 */
	public function history(): \Iterator {
		return new Iterator\Mapped(
			$this->origin->history(),
			function(Spot $spot): Spot {
				return new PublicSpot($spot, $this->spotHashids);
			}
		);
	}
}
