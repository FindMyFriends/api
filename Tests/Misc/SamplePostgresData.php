<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SamplePostgresData implements Sample {
	private $database;
	private $sample;
	private $data;

	public function __construct(\PDO $database, string $sample, array $data = []) {
		$this->database = $database;
		$this->sample = $sample;
		$this->data = $data;
	}

	public function try(): array {
		return (new Storage\NativeQuery(
			$this->database,
			sprintf('SELECT samples.%s(?) AS id', $this->sample),
			[json_encode($this->data, JSON_FORCE_OBJECT)]
		))->row();
	}
}