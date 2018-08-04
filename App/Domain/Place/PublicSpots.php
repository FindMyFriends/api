<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Hashids\HashidsInterface;
use Klapuch\Iterator;

/**
 * Locations formatted to be used for public representation
 */
final class PublicLocations implements Locations {
	/** @var \FindMyFriends\Domain\Place\Locations */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $locationHashids;

	public function __construct(Locations $origin, HashidsInterface $locationHashids) {
		$this->origin = $origin;
		$this->locationHashids = $locationHashids;
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
				return new PublicLocation($location, $this->locationHashids);
			}
		);
	}
}
