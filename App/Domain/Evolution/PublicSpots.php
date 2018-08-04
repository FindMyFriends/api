<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Place;
use Hashids\HashidsInterface;
use Klapuch\Iterator;

/**
 * Spots formatted to be used for public representation
 */
final class PublicSpots implements Place\Spots {
	/** @var \FindMyFriends\Domain\Place\Spots */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $spotHashids;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	public function __construct(
		Place\Spots $origin,
		HashidsInterface $spotHashids,
		HashidsInterface $evolutionHashids
	) {
		$this->origin = $origin;
		$this->spotHashids = $spotHashids;
		$this->evolutionHashids = $evolutionHashids;
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
			function(Place\Spot $spot): Place\Spot {
				return new PublicSpot(
					new Place\PublicSpot($spot, $this->spotHashids),
					$this->evolutionHashids
				);
			}
		);
	}
}
