<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleLocation implements Sample {
	private $database;
	private $location;

	public function __construct(\PDO $database, array $location = []) {
		$this->database = $database;
		$this->location = $location;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO locations (coordinates, place, met_at) VALUES
			(?, ?, ?)
			RETURNING id',
			[
				$this->location['coordinates'] ?? sprintf('(%1$d.%2$d,%2$d.%1$d)', mt_rand(0, 50), mt_rand(50, 99)),
				$this->location['place'] ?? bin2hex(random_bytes(10)),
				$this->location['met_at'] ?? sprintf('[2017-01-01,2017-01-02)'),
			]
		))->row();
	}
}