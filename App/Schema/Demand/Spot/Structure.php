<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Demand\Spot;

use FindMyFriends\Schema;

final class Structure {
	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$spot = (new Schema\Spot\Structure($this->database))->get();
		$spot['properties']['demand_id'] = ['type' => 'string'];
		$spot['required'] = array_merge_recursive($spot['required'], ['demand_id']);
		return $spot;
	}

	public function post(): array {
		return (new Schema\Spot\Structure($this->database))->post();
	}
}