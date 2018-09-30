<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;

interface Databases {
	/**
	 * Create a new database
	 * @return \Klapuch\Storage\Connection
	 */
	public function create(): Storage\Connection;

	/**
	 * Drop the database
	 * @return void
	 */
	public function drop(): void;
}
