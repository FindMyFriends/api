<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

use Klapuch\Storage;

final class Colors implements Enum {
	private $column;
	private $database;
	private $set;

	public function __construct(string $column, string $set, \PDO $database) {
		$this->column = $column;
		$this->database = $database;
		$this->set = $set;
	}

	public function values(): array {
		return array_column(
			(new Storage\NativeQuery(
				$this->database,
				sprintf(
					'SELECT * FROM %1$s
					JOIN colors ON colors.id = %1$s.color_id
					ORDER BY hex DESC',
					$this->set
				)
			))->rows(),
			$this->column
		);
	}
}