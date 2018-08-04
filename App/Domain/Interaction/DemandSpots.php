<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Place;
use FindMyFriends\Sql\CollectiveDemandSpots;
use Klapuch\Storage;

/**
 * All the spots from single demand
 */
class DemandSpots implements Place\Spots {
	/** @var int */
	private $demand;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $demand, Storage\MetaPDO $database) {
		$this->demand = $demand;
		$this->database = $database;
	}

	public function track(array $spot): void {
		(new Storage\TypedQuery(
			$this->database,
			'INSERT INTO collective_demand_spots (demand_id, coordinates, met_at) VALUES
			(:demand_id, POINT(:latitude, :longitude), ROW(:moment, :timeline_side, :approximation))',
			['demand_id' => $this->demand] + $spot['coordinates'] + $spot['met_at']
		))->execute();
	}

	public function history(): \Iterator {
		$spots = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveDemandSpots\Select())
				->from(['collective_demand_spots'])
				->where('demand_id = ?', [$this->demand])
		))->rows();
		foreach ($spots as $spot) {
			yield new StoredSpot(
				$spot['id'],
				new Storage\MemoryPDO($this->database, $spot)
			);
		}
	}
}
