<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

interface Databases {
	/**
	 * Create a new database
	 * @return \PDO
	 */
	public function create(): \PDO;

	/**
	 * Drop the database
	 * @return void
	 */
	public function drop(): void;
}