<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleBody implements Sample {
	private $database;
	private $body;

	public function __construct(\PDO $database, array $body = []) {
		$this->database = $database;
		$this->body = $body;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO bodies (build, skin, weight, height) VALUES
			(?, ? , ?, ?)
			RETURNING id',
			[
				$this->body['build'] ?? bin2hex(random_bytes(40)),
				$this->body['skin'] ?? substr(uniqid('', true), 0, mt_rand(1, 50)),
				$this->body['weight'] ?? mt_rand(),
				$this->body['height'] ?? mt_rand(),
			]
		))->row();
	}
}