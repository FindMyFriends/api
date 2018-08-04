<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Place;
use FindMyFriends\Sql\CollectiveDemandSpots;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Stored spot
 */
final class StoredSpot implements Place\Spot {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function forget(): void {
		(new Storage\TypedQuery(
			$this->database,
			'DELETE FROM demand_spots WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$spot = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveDemandSpots\Select())
				->from(['collective_demand_spots'])
				->where('id = ?', [$this->id])
		))->row();
		return $format->with('demand_id', $spot['demand_id'])
			->with('id', $spot['id'])
			->with(
				'coordinates',
				[
					'latitude' => $spot['coordinates']['x'],
					'longitude' => $spot['coordinates']['y'],
				]
			)
			->with('met_at', $spot['met_at'])
			->with('assigned_at', $spot['assigned_at']);
	}
}
