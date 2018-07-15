<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Hashids\HashidsInterface;
use Klapuch\Iterator;

/**
 * Locations formatted to be used for public representation
 */
final class PublicLocations implements Locations {
	/** @var \FindMyFriends\Domain\Evolution\Locations */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $locationHashids;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	public function __construct(
		Locations $origin,
		HashidsInterface $locationHashids,
		HashidsInterface $evolutionHashids
	) {
		$this->origin = $origin;
		$this->locationHashids = $locationHashids;
		$this->evolutionHashids = $evolutionHashids;
	}

	/**
	 * @param mixed[] $location
	 * @throws \UnexpectedValueException
	 */
	public function track(array $location): void {
		$this->origin->track($location);
	}

	/**
	 * @return \Iterator
	 * @throws \UnexpectedValueException
	 */
	public function history(): \Iterator {
		return new Iterator\Mapped(
			$this->origin->history(),
			function(Location $location): Location {
				return new PublicLocation(
					$location,
					$this->locationHashids,
					$this->evolutionHashids
				);
			}
		);
	}
}
