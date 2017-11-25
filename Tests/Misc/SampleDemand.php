<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleDemand implements Sample {
	private $demand;
	private $database;

	public function __construct(\PDO $database, array $demand = []) {
		$this->demand = $demand;
		$this->database = $database;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES
			(?, ?, ?, ?)
			RETURNING id',
			[
				$this->demand['seeker'] ?? $this->demand['seeker_id'] ?? current((new SampleSeeker($this->database))->try()),
				current(
					(new SampleDescription(
						$this->database,
						[
							'general' => current((new SampleGeneral($this->database, $this->demand['general'] ?? []))->try()),
							'body' => current((new SampleBody($this->database, $this->demand['body'] ?? []))->try()),
							'face' => current((new SampleFace($this->database, $this->demand['face'] ?? []))->try()),
							'hand' => current((new SampleHand($this->database, $this->demand['hand'] ?? []))->try()),
						]
					))->try()
				),
				isset($this->demand['created_at']) ? $this->demand['created_at']->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
				current((new SampleLocation($this->database, $this->demand['location'] ?? []))->try()),
			]
		))->row();
	}
}