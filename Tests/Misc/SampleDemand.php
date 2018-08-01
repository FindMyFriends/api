<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use FindMyFriends\Misc;
use Klapuch\Storage;

final class SampleDemand implements Sample {
	/** @var mixed[] */
	private $demand;

	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database, array $demand = []) {
		$this->demand = $demand;
		$this->database = $database;
	}

	public function try(): array {
		return (new Storage\NativeQuery(
			$this->database,
			'INSERT INTO demands (seeker_id, description_id, created_at) VALUES
			(?, ?, ?)
			RETURNING id',
			[
				$this->demand['seeker'] ?? $this->demand['seeker_id'] ?? current((new Misc\SamplePostgresData($this->database, 'seeker'))->try()),
				current(
					(new SampleDescription(
						$this->database,
						[
							'general_id' => current((new SamplePostgresData($this->database, 'general', $this->demand['general'] ?? []))->try()),
							'body_id' => current((new SamplePostgresData($this->database, 'body', $this->demand['body'] ?? []))->try()),
							'face_id' => current((new SamplePostgresData($this->database, 'face', $this->demand['face'] ?? []))->try()),
							'hand_id' => current((new SamplePostgresData($this->database, 'hand', $this->demand['hand'] ?? []))->try()),
							'hair_id' => current((new SamplePostgresData($this->database, 'hair', $this->demand['hair'] ?? []))->try()),
							'beard_id' => current((new SamplePostgresData($this->database, 'beard', $this->demand['beard'] ?? []))->try()),
							'eyebrow_id' => current((new SamplePostgresData($this->database, 'eyebrow', $this->demand['eyebrow'] ?? []))->try()),
							'tooth_id' => current((new SamplePostgresData($this->database, 'tooth', $this->demand['tooth'] ?? []))->try()),
							'left_eye_id' => current((new SamplePostgresData($this->database, 'eye', $this->demand['left_eye'] ?? []))->try()),
							'right_eye_id' => current((new SamplePostgresData($this->database, 'eye', $this->demand['right_eye'] ?? []))->try()),
						]
					))->try()
				),
				isset($this->demand['created_at']) ? $this->demand['created_at']->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
			]
		))->row();
	}
}
