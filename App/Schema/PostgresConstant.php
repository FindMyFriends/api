<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class PostgresConstant implements Enum {
	/** @var string */
	private $name;

	/** @var \PDO */
	private $database;

	public function __construct(string $name, \PDO $database) {
		$this->name = $name;
		$this->database = $database;
	}

	public function values(): array {
		return array_column(
			(new Storage\NativeQuery(
				$this->database,
				sprintf('SELECT unnest(constant.%s()) AS values ORDER BY 1', $this->name)
			))->rows(),
			'values'
		);
	}
}
