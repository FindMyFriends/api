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
			'INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES
			(?, ?, ?, ?, ?)
			RETURNING id',
			[
				$this->general['gender'] ?? self::GENDERS[array_rand(self::GENDERS)],
				$this->general['race'] ?? self::RACES[array_rand(self::RACES)],
				$this->general['birth_year'] ?? sprintf('[%d,%d)', mt_rand(1850, 1900), mt_rand(1901, 2017)),
				$this->general['firstname'] ?? bin2hex(random_bytes(10)),
				$this->general['lastname'] ?? bin2hex(random_bytes(10)),
			]
		))->row();
	}
}