<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql;

final class KeyValueMapping implements Mapping {
	/** @var string[] */
	private $map;

	public function __construct(array $map) {
		$this->map = $map;
	}

	public function application(array $connection): array {
		$keys = array_intersect_key($this->map, $connection);
		ksort($connection);
		ksort($keys);
		return array_combine($keys, $connection);
	}

	public function database(array $application): array {
		$keys = array_intersect_key(array_flip($this->map), $application);
		ksort($keys);
		ksort($application);
		return array_combine($keys, $application);
	}
}
