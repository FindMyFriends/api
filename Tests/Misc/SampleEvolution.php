<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use FindMyFriends\Misc;
use Klapuch\Storage;

final class SampleEvolution implements Sample {
	private $evolution;
	private $database;

	public function __construct(\PDO $database, array $evolution = []) {
		$this->evolution = $evolution;
		$this->database = $database;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (?, ?, ?)
			RETURNING id',
			[
				$this->evolution['seeker_id'] ?? current((new Misc\SamplePostgresData($this->database, 'seeker'))->try()),
				current(
					(new SampleDescription(
						$this->database,
						[
							'general_id' => current(
								(new SamplePostgresData(
									$this->database,
									'general',
									$this->evolution['general'] ?? []
								))->try()
							),
							'body_id' => current(
								(new SamplePostgresData(
									$this->database,
									'body',
									$this->evolution['body'] ?? []
								))->try()
							),
							'face_id' => current(
								(new SamplePostgresData(
									$this->database,
									'face',
									$this->evolution['face'] ?? []
								))->try()
							),
							'hand_id' => current(
								(new SamplePostgresData(
									$this->database,
									'hand',
									$this->evolution['hand'] ?? []
								))->try()
							),
							'hair_id' => current(
								(new SamplePostgresData(
									$this->database,
									'hair',
									$this->evolution['hand'] ?? []
								))->try()
							),
						]
					))->try()
				),
				isset($this->evolution['evolved_at']) ? $this->evolution['evolved_at']->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
			]
		))->row();
	}
}