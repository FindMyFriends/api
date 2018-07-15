<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

/**
 * Locations called sequentially one by one behaving as a single one
 */
final class ChainedLocations implements Locations {
	/** @var \FindMyFriends\Domain\Evolution\Locations[] */
	private $origins;

	public function __construct(Locations ...$origins) {
		$this->origins = $origins;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @param mixed[] $location
	 */
	public function track(array $location): void {
		foreach ($this->origins as $origin) {
			$origin->track($location);
		}
	}

	public function history(): \Iterator {
	}
}
