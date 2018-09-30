<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use FindMyFriends\Misc;
use Klapuch\Storage;

final class SampleEvolution implements Sample {
	/** @var mixed[] */
	private $evolution;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection, array $evolution = []) {
		$this->evolution = $evolution;
		$this->connection = $connection;
	}

	public function try(): array {
		return (new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (?, ?, ?)
			RETURNING id',
			[
				$this->evolution['seeker_id'] ?? current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try()),
				current(
					(new SampleDescription(
						$this->connection,
						[
							'general_id' => current((new SamplePostgresData($this->connection, 'general', ($this->evolution['general'] ?? []) + ['birth_year' => rand(1991, 1999)]))->try()),
							'body_id' => current((new SamplePostgresData($this->connection, 'body', $this->evolution['body'] ?? []))->try()),
							'face_id' => current((new SamplePostgresData($this->connection, 'face', $this->evolution['face'] ?? []))->try()),
							'hand_id' => current((new SamplePostgresData($this->connection, 'hand', $this->evolution['hand'] ?? []))->try()),
							'hair_id' => current((new SamplePostgresData($this->connection, 'hair', $this->evolution['hand'] ?? []))->try()),
							'beard_id' => current((new SamplePostgresData($this->connection, 'beard', $this->evolution['beard'] ?? []))->try()),
							'eyebrow_id' => current((new SamplePostgresData($this->connection, 'eyebrow', $this->evolution['eyebrow'] ?? []))->try()),
							'tooth_id' => current((new SamplePostgresData($this->connection, 'tooth', $this->evolution['tooth'] ?? []))->try()),
							'left_eye_id' => current((new SamplePostgresData($this->connection, 'eye', $this->evolution['left_eye'] ?? []))->try()),
							'right_eye_id' => current((new SamplePostgresData($this->connection, 'eye', $this->evolution['right_eye'] ?? []))->try()),
						]
					))->try()
				),
				isset($this->evolution['evolved_at']) ? $this->evolution['evolved_at']->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
			]
		))->row();
	}
}
