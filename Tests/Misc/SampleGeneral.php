<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleGeneral implements Sample {
	private const GENDERS = ['man', 'woman'];
	private const RACES = ['european', 'asian', 'other'];
	private $database;
	private $general;

	public function __construct(\PDO $database, array $general = []) {
		$this->database = $database;
		$this->general = $general;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			"INSERT INTO general (gender, race, age, firstname, lastname) VALUES
			(?, ?, ?, ?, ?)
			RETURNING id",
			[
				$this->general['gender'] ?? self::GENDERS[array_rand(self::GENDERS)],
				$this->general['race'] ?? self::RACES[array_rand(self::RACES)],
				$this->general['age'] ?? sprintf('[%d,%d)', mt_rand(0, 50), mt_rand(51, 100)),
				$this->general['firstname'] ?? bin2hex(random_bytes(10)),
				$this->general['lastname'] ?? bin2hex(random_bytes(10)),
			]
		))->row();
	}
}