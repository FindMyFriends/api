<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

interface Publisher {
	/**
	 * Publish the demand
	 * @throws \UnexpectedValueException
	 * @param int $demand
	 */
	public function publish(int $demand): void;
}
