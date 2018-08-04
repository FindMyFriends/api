<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

interface Spots {
	/**
	 * Track new spot
	 * @param mixed[] $spot
	 * @throws \UnexpectedValueException
	 */
	public function track(array $spot): void;

	/**
	 * History of spots
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function history(): \Iterator;
}
