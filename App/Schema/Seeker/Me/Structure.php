<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker\Me;

use FindMyFriends\Schema;
use Klapuch\Storage;

final class Structure {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function get(): array {
		return (new Schema\Seeker\Structure($this->connection))->get();
	}
}
