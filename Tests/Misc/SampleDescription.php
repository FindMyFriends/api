<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleDescription implements Sample {
	/** @var mixed[] */
	private $description;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection, array $description = []) {
		$this->description = $description;
		$this->connection = $connection;
	}

	public function try(): array {
		return (new Storage\NativeQuery(
			$this->connection,
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
