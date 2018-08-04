<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;

/**
 * Location called sequentially one by one behaving as a single one
 */
final class ChainedSpot implements Location {
	/** @var \FindMyFriends\Domain\Place\Location[] */
	private $locations;

	public function __construct(Location ...$locations) {
		$this->locations = $locations;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void {
		foreach ($this->locations as $location) {
			$location->forget();
		}
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}
