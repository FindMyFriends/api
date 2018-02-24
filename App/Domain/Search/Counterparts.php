<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

interface Counterparts {
	/**
	 * Find all counterparts to your demand
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function find(): void;
}