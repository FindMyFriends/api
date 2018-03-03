<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

interface SoulMates {
	/**
	 * Find all soul mates to your demand
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function find(): void;
}