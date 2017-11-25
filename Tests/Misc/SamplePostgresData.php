<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SamplePostgresData implements Sample {
	private $database;
	private $table;
	private $data;

	public function __construct(\PDO $database, string $table, array $data = []) {
		$this->database = $database;
		$this->table = $table;
		$this->data = $data;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			sprintf(
				'SELECT %1$s AS id FROM samples.%1$s(test_utils.json_to_hstore(?))',
				$this->table
			),
			[json_encode($this->data, JSON_FORCE_OBJECT)]
		))->row();
	}
}