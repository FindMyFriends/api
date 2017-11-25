<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

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
				$this->evolution['seeker'] ?? $this->evolution['seeker_id'] ?? current((new SampleSeeker($this->database))->try()),
				current(
					(new SampleDescription(
						$this->database,
						[
							'general' => current(
								(new SampleGeneral(
									$this->database,
									$this->evolution['general'] ?? []
								))->try()
							),
							'body' => current(
								(new SampleBody(
									$this->database,
									$this->evolution['body'] ?? []
								))->try()
							),
							'face' => current(
								(new SampleFace(
									$this->database,
									$this->evolution['face'] ?? []
								))->try()
							),
							'hand' => current(
								(new SampleHand(
									$this->database,
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