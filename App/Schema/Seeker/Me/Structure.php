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
		$get = (new Schema\Seeker\Structure($this->connection))->get();
		$get['required'] = array_merge($get['required'], ['email']);
		return $get;
	}
}
