<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

use Klapuch\Storage;

final class PostgresTableEnum implements Enum {
	private $column;
	private $table;
	private $database;

	public function __construct(string $column, string $table, \PDO $database) {
		$this->column = $column;
		$this->table = $table;
		$this->database = $database;
	}

	public function values(): array {
		return array_column(
			(new Storage\NativeQuery(
				$this->database,
				sprintf('SELECT %s FROM %s ORDER BY id', $this->column, $this->table)
			))->rows(),
			$this->column
		);
	}
}