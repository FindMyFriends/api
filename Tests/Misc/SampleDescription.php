<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleDescription implements Sample {
	private $description;
	private $database;

	public function __construct(\PDO $database, array $description = []) {
		$this->description = $description;
		$this->database = $database;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO descriptions (general_id, body_id, face_id, hands_id) VALUES 
			(?, ?, ?, ?)
			RETURNING id',
			[
				$this->description['general'] ?? $this->description['general_id'] ?? mt_rand(),
				$this->description['body'] ?? $this->description['body_id'] ?? mt_rand(),
				$this->description['face'] ?? $this->description['face_id'] ?? mt_rand(),
				$this->description['hand'] ?? $this->description['hand_id'] ?? mt_rand(),
			]
		))->row();
	}
}