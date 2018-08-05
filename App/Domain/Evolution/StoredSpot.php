<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Place;
use FindMyFriends\Sql\CollectiveEvolutionSpots;
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
			'DELETE FROM evolution_spots WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$spot = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveEvolutionSpots\Select())
				->from(['collective_evolution_spots'])
				->where('id = ?', [$this->id])
		))->row();
		return $format->with('evolution_id', $spot['evolution_id'])
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

	public function move(array $movement): void {
		// no direct movements
	}
}
