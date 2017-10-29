<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleSeeker implements Sample {
	private $database;
	private $seekers;

	public function __construct(\PDO $database, array $seekers = []) {
		$this->database = $database;
		$this->seekers = $seekers;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO seekers (id, email, password) VALUES
			(?, ?, ?)
			RETURNING id',
			[
				$this->seekers['id'] ?? mt_rand(),
				$this->seekers['email'] ?? sprintf(
					'%s@gmail.com',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				$this->seekers['password'] ?? password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT),
			]
		))->row();
	}
}