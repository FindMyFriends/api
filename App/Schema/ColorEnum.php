<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Storage;

final class ColorEnum implements Enum {
	private $set;
	private $database;

	public function __construct(string $set, \PDO $database) {
		$this->set = $set;
		$this->database = $database;
	}

	public function values(): array {
		return array_reduce(
			(new Storage\NativeQuery(
				$this->database,
				sprintf(
					'SELECT color_id, name, hex
					FROM %1$s
					JOIN colors ON colors.id = %1$s.color_id
					ORDER BY color_id ASC',
					$this->set
				)
			))->rows(),
			function(array $enum, array $color): array {
				$enum[$color['color_id']] = [
					'name' => $color['name'],
					'hex' => $color['hex'],
				];
				return $enum;
			},
			[]
		);
	}
}
