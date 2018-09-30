<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class PostgresEnum implements Enum {
	/** @var string */
	private $name;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(string $name, Storage\Connection $connection) {
		$this->name = $name;
		$this->connection = $connection;
	}

	public function values(): array {
		return array_column(
			(new Storage\NativeQuery(
				$this->connection,
				sprintf('SELECT unnest(enum_range(NULL::%s)) AS values ORDER BY 1', $this->name)
			))->rows(),
			'values'
		);
	}
}
