<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class ColorEnum implements Enum {
	/** @var string */
	private $set;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(string $set, Storage\Connection $connection) {
		$this->set = $set;
		$this->connection = $connection;
	}

	public function values(): array {
		$colors = (new Storage\NativeQuery(
			$this->connection,
			sprintf(
				'SELECT color_id AS id, name, hex
				FROM %1$s
				JOIN colors ON colors.id = %1$s.color_id
				ORDER BY color_id ASC',
				$this->set
			)
		))->rows();
		return array_combine(array_column($colors, 'id'), $colors);
	}
}
