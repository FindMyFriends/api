<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Place;
use FindMyFriends\Sql\CollectiveEvolutionSpots;
use Klapuch\Storage;

/**
 * All the spots from single evolution change
 */
class ChangeSpots implements Place\Spots {
	/** @var int */
	private $change;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $change, Storage\MetaPDO $database) {
		$this->change = $change;
		$this->database = $database;
	}

	public function track(array $spot): void {
		(new Storage\TypedQuery(
			$this->database,
			'INSERT INTO collective_evolution_spots (evolution_id, coordinates, met_at) VALUES
			(:evolution_id, POINT(:latitude, :longitude), ROW(:moment, :timeline_side, :approximation))',
			['evolution_id' => $this->change] + $spot['coordinates'] + $spot['met_at']
		))->execute();
	}

	public function history(): \Iterator {
		$spots = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveEvolutionSpots\Select())
				->from(['collective_evolution_spots'])
				->where('evolution_id = ?', [$this->change])
		))->rows();
		foreach ($spots as $spot) {
			yield new StoredSpot(
				$spot['id'],
				new Storage\MemoryPDO($this->database, $spot)
			);
		}
	}
}
