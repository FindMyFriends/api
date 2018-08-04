<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Place;
use FindMyFriends\Sql\CollectiveDemandLocations;
use Klapuch\Storage;

/**
 * All the locations from single demand
 */
class DemandLocations implements Place\Locations {
	/** @var int */
	private $demand;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $demand, Storage\MetaPDO $database) {
		$this->demand = $demand;
		$this->database = $database;
	}

	public function track(array $location): void {
		(new Storage\TypedQuery(
			$this->database,
			'INSERT INTO collective_demand_locations (demand_id, coordinates, met_at) VALUES
			(:demand_id, POINT(:latitude, :longitude), ROW(:moment, :timeline_side, :approximation))',
			['demand_id' => $this->demand] + $location['coordinates'] + $location['met_at']
		))->execute();
	}

	public function history(): \Iterator {
		$locations = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveDemandLocations\Select())
				->from(['collective_demand_locations'])
				->where('demand_id = ?', [$this->demand])
		))->rows();
		foreach ($locations as $location) {
			yield new StoredLocation(
				$location['id'],
				new Storage\MemoryPDO($this->database, $location)
			);
		}
	}
}
