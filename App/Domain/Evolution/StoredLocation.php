<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Sql\CollectiveEvolutionLocations;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Stored location
 */
final class StoredLocation implements Location {
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
			'DELETE FROM locations WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$location = (new Storage\BuiltQuery(
			$this->database,
			(new CollectiveEvolutionLocations\Select())
				->from(['collective_evolution_locations'])
				->where('id = ?', [$this->id])
		))->row();
		return $format->with('evolution_id', $location['evolution_id'])
			->with('id', $location['id'])
			->with(
				'coordinates',
				[
					'latitude' => $location['coordinates']['x'],
					'longitude' => $location['coordinates']['y'],
				]
			)
			->with('met_at', $location['met_at'])
			->with('assigned_at', $location['assigned_at']);
	}
}
