<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

/**
 * Spots called sequentially one by one behaving as a single one
 */
final class ChainedSpots implements Spots {
	/** @var \FindMyFriends\Domain\Place\Spots[] */
	private $origins;

	public function __construct(Spots ...$origins) {
		$this->origins = $origins;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @param mixed[] $spot
	 */
	public function track(array $spot): void {
		foreach ($this->origins as $origin) {
			$origin->track($spot);
		}
	}

	public function history(): \Iterator {
	}
}
