<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class TableEnum implements Enum {
	/** @var string */
	private $table;

	/** @var \PDO */
	private $database;

	public function __construct(string $table, \PDO $database) {
		$this->table = $table;
		$this->database = $database;
	}

	public function values(): array {
		$enum = (new Storage\NativeQuery(
			$this->database,
			sprintf('SELECT id, name FROM %s ORDER BY id', $this->table)
		))->rows();
		return array_combine(array_column($enum, 'id'), $enum);
	}
}
