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
		return (new Storage\NativeQuery(
			$this->database,
			'INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, beard_id, eyebrow_id, tooth_id, left_eye_id, right_eye_id) VALUES 
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			RETURNING id',
			[
				$this->description['general_id'],
				$this->description['body_id'],
				$this->description['face_id'],
				$this->description['hand_id'],
				$this->description['hair_id'],
				$this->description['beard_id'],
				$this->description['eyebrow_id'],
				$this->description['tooth_id'],
				$this->description['left_eye_id'],
				$this->description['right_eye_id'],
			]
		))->row();
	}
}