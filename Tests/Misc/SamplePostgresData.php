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
		return (new Storage\NativeQuery(
			$this->database,
			sprintf('SELECT samples.%s(?) AS id', $this->table),
			[json_encode($this->data, JSON_FORCE_OBJECT)]
		))->row();
	}
}