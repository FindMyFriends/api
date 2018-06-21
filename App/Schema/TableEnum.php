<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class TableEnum implements Enum {
	private $table;
	private $database;

	public function __construct(string $table, \PDO $database) {
		$this->table = $table;
		$this->database = $database;
	}

	public function values(): array {
		return (new Storage\NativeQuery(
			$this->database,
			sprintf('SELECT id, name FROM %s ORDER BY id', $this->table)
		))->rows();
	}
}
