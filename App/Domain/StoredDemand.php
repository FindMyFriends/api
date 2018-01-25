<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use FindMyFriends\Sql;
use Klapuch\Output;
use Klapuch\Storage;

final class StoredDemand implements Demand {
	private $id;
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$demand = (new Storage\TypedQuery(
			$this->database,
			(new Sql\Demand\Select())
				->from(['collective_demands'])
				->where('id = ?')
				->sql(),
			[$this->id]
		))->row();
		return (new CompleteDescription($format, $demand))
			->with('id', $demand['id'])
			->with('seeker_id', $demand['seeker_id'])
			->with('created_at', $demand['created_at'])
			->with(
				'location',
				[
					'coordinates' => [
						'latitude' => $demand['location_coordinates']['x'],
						'longitude' => $demand['location_coordinates']['y'],
					],
					'met_at' => $demand['location_met_at'],
				]
			);
	}

	public function retract(): void {
		(new Storage\NativeQuery(
			$this->database,
			'DELETE FROM demands WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function reconsider(array $description): void {
		(new Storage\FlatQuery(
			$this->database,
			(new Sql\Demand\Set(
				new Storage\Clauses\AnsiUpdate('collective_demands')
			))->where('id = :id')->sql(),
			['id' => $this->id] + $description
		))->execute();
	}
}