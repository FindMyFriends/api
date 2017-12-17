<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

use Klapuch\Storage;

final class Colors implements Enum {
	private $column;
	private $database;
	private $purpose;

	public function __construct(string $column, string $purpose, \PDO $database) {
		$this->column = $column;
		$this->database = $database;
		$this->purpose = $purpose;
	}

	public function values(): array {
		return array_column(
			(new Storage\NativeQuery(
				$this->database,
				'SELECT * FROM colors WHERE ? = ANY(purpose) ORDER BY id',
				[$this->purpose]
			))->rows(),
			$this->column
		);
	}
}