<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class TableEnum implements Enum {
	/** @var string */
	private $table;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(string $table, Storage\Connection $connection) {
		$this->table = $table;
		$this->connection = $connection;
	}

	public function values(): array {
		$enum = (new Storage\NativeQuery(
			$this->connection,
			sprintf('SELECT id, name FROM %s ORDER BY id', $this->table)
		))->rows();
		return array_combine(array_column($enum, 'id'), $enum);
	}
}
