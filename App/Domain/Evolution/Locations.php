<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

interface Locations {
	/**
	 * Track new location
	 * @param mixed[] $location
	 * @throws \UnexpectedValueException
	 */
	public function track(array $location): void;

	/**
	 * History of locations
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function history(): \Iterator;
}
