<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

use Klapuch\Storage;

final class PostgresTableEnum implements Enum {
	private $table;
	private $database;

	public function __construct(string $table, \PDO $database) {
		$this->table = $table;
		$this->database = $database;
	}

	public function values(): array {
		return array_column(
			(new Storage\ParameterizedQuery(
				$this->database,
				sprintf('SELECT id FROM %s', $this->table)
			))->rows(),
			'id'
		);
	}
}