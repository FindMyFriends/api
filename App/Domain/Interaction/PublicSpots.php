<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Place;
use Hashids\HashidsInterface;
use Klapuch\Iterator;

/**
 * Locations formatted to be used for public representation
 */
final class PublicLocations implements Place\Locations {
	/** @var \FindMyFriends\Domain\Place\Locations */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $locationHashids;

	/** @var \Hashids\HashidsInterface */
	private $demandHashids;

	public function __construct(
		Place\Locations $origin,
		HashidsInterface $locationHashids,
		HashidsInterface $demandHashids
	) {
		$this->origin = $origin;
		$this->locationHashids = $locationHashids;
		$this->demandHashids = $demandHashids;
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
			function(Place\Location $location): Place\Location {
				return new PublicLocation(
					new Place\PublicLocation($location, $this->locationHashids),
					$this->demandHashids
				);
			}
		);
	}
}
