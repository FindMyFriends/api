<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use FindMyFriends\Misc;
use Klapuch\Storage;

final class SampleDemand implements Sample {
	/** @var mixed[] */
	private $demand;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection, array $demand = []) {
		$this->demand = $demand;
		$this->connection = $connection;
	}

	public function try(): array {
		return (new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO demands (seeker_id, description_id, created_at) VALUES
			(?, ?, ?)
			RETURNING id',
			[
				$this->demand['seeker'] ?? $this->demand['seeker_id'] ?? current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try()),
				current(
					(new SampleDescription(
						$this->connection,
						[
							'general_id' => current((new SamplePostgresData($this->connection, 'general', ($this->demand['general'] ?? []) + ['birth_year_range' => '[1996,1999]']))->try()),
							'body_id' => current((new SamplePostgresData($this->connection, 'body', $this->demand['body'] ?? []))->try()),
							'face_id' => current((new SamplePostgresData($this->connection, 'face', $this->demand['face'] ?? []))->try()),
							'hand_id' => current((new SamplePostgresData($this->connection, 'hand', $this->demand['hand'] ?? []))->try()),
							'hair_id' => current((new SamplePostgresData($this->connection, 'hair', $this->demand['hair'] ?? []))->try()),
							'beard_id' => current((new SamplePostgresData($this->connection, 'beard', $this->demand['beard'] ?? []))->try()),
							'eyebrow_id' => current((new SamplePostgresData($this->connection, 'eyebrow', $this->demand['eyebrow'] ?? []))->try()),
							'tooth_id' => current((new SamplePostgresData($this->connection, 'tooth', $this->demand['tooth'] ?? []))->try()),
							'left_eye_id' => current((new SamplePostgresData($this->connection, 'eye', $this->demand['left_eye'] ?? []))->try()),
							'right_eye_id' => current((new SamplePostgresData($this->connection, 'eye', $this->demand['right_eye'] ?? []))->try()),
						]
					))->try()
				),
				isset($this->demand['created_at']) ? $this->demand['created_at']->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
			]
		))->row();
	}
}
