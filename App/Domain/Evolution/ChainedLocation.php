<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;

/**
 * Location called sequentially one by one behaving as a single one
 */
final class ChainedLocation implements Location {
	/** @var \FindMyFriends\Domain\Evolution\Location[] */
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
