<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

interface Relationships {
	/**
	 * Find all relationships to your demand
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function find(): void;
}