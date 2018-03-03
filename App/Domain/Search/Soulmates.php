<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

interface Soulmates {
	/**
	 * Find all soulmates to your demand
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function find(): void;
}