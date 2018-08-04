<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Place;
use FindMyFriends\Sql\CollectiveEvolutionLocations;
use Klapuch\Storage;

/**
 * All the locations from single evolution change
 */
class ChangeLocations implements Place\Locations {
	/** @var int */
	private $change;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $change, Storage\MetaPDO $database) {
		$this->change = $change;
		$this->database = $database;
	}

	public function track(array $location): void {
		(new Storage\TypedQuery(
			$this->database,
			'INSERT INTO collective_evolution_locations (evolution_id, coordinates, met_at) VALUES
			(:evolution_id, POINT(:latitude, :longitude), ROW(:moment, :timeline_side, :approximation))',
			['evolution_id' => $this->change] + $location['coordinates'] + $location['met_at']
		))->execute();
	}

	public function history(): \Iterator {
		$locations = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveEvolutionLocations\Select())
				->from(['collective_evolution_locations'])
				->where('evolution_id = ?', [$this->change])
		))->rows();
		foreach ($locations as $location) {
			yield new StoredLocation(
				$location['id'],
				new Storage\MemoryPDO($this->database, $location)
			);
		}
	}
}
