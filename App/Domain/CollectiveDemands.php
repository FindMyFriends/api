<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Demands belonging to everyone
 */
final class CollectiveDemands implements Demands {
	private $origin;
	private $database;

	public function __construct(Demands $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function ask(array $description): Demand {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\TypedQuery(
			$this->database,
			new Storage\ParameterizedQuery(
				$this->database,
				$selection->expression(
					'SELECT demands.id, demands.seeker_id, demands.created_at,
					bodies.build, bodies.skin, bodies.weight, bodies.height,
					faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
					general.birth_year, general.firstname, general.lastname, general.gender, general.race,
					locations.coordinates, locations.met_at
					FROM demands
					JOIN locations ON locations.id = demands.location_id
					JOIN descriptions ON descriptions.id = demands.description_id
					JOIN bodies ON bodies.id = descriptions.body_id
					JOIN faces ON faces.id = descriptions.face_id
					JOIN general ON general.id = descriptions.general_id'
				),
				$selection->criteria([])
			),
			[
				'hair' => 'hair',
				'left_eye' => 'eye',
				'right_eye' => 'eye',
				'teeth' => 'tooth',
				'coordinates' => 'point',
			]
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO($this->database, $demand)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM demands'),
			$selection->criteria([])
		))->field();
	}
}