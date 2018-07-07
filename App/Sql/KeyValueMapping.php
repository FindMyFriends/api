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
		$keys = array_intersect_key($this->map, $database);
		ksort($database);
		ksort($keys);
		return array_combine($keys, $database);
	}

	public function database(array $application): array {
		$keys = array_intersect_key(array_flip($this->map), $application);
		ksort($keys);
		ksort($application);
		return array_combine($keys, $application);
	}
}
