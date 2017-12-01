<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

use Klapuch\Storage;

final class PostgresEnum implements Enum {
	private $name;
	private $database;

	public function __construct(string $name, \PDO $database) {
		$this->name = $name;
		$this->database = $database;
	}

	public function values(): array {
		return array_column(
			(new Storage\ParameterizedQuery(
				$this->database,
				sprintf('SELECT unnest(enum_range(NULL::%s)) AS values', $this->name)
			))->rows(),
			'values'
		);
	}
}