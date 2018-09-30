<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Evolution\Spot;

use FindMyFriends\Schema;
use Klapuch\Storage;

final class Structure {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function get(): array {
		$spot = (new Schema\Spot\Structure($this->connection))->get();
		$spot['properties']['evolution_id'] = ['type' => 'string'];
		$spot['required'] = array_merge_recursive($spot['required'], ['evolution_id']);
		return $spot;
	}

	public function post(): array {
		return (new Schema\Spot\Structure($this->connection))->post();
	}
}
