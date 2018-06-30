<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql;

final class KeyValueMapping implements Mapping {
	/** @var string[] */
	private $map;

	public function __construct(array $map) {
		$this->map = $map;
	}

	public function application(array $database): array {
		return array_combine(
			array_intersect_key($this->map, $database),
			$database
		);
	}

	public function database(array $application): array {
		return array_combine(
			array_intersect_key(array_flip($this->map), $application),
			$application
		);
	}
}
