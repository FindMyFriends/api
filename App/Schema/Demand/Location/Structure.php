<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Demand\Location;

use FindMyFriends\Schema;

final class Structure {
	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$location = (new Schema\Location\Structure($this->database))->get();
		$location['properties']['demand_id'] = ['type' => 'string'];
		$location['required'] = array_merge_recursive($location['required'], ['demand_id']);
		return $location;
	}

	public function post(): array {
		return (new Schema\Location\Structure($this->database))->post();
	}
}
