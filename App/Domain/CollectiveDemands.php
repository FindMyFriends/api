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
		$demands = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT demands.id, demands.seeker_id, demands.created_at,
				bodies.build, bodies.skin, bodies.weight, bodies.height,
				faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
				general.birth_year, general.firstname, general.lastname, general.gender, general.race
				FROM demands
				JOIN descriptions ON descriptions.id = demands.description_id
				JOIN bodies ON bodies.id = descriptions.body_id
				JOIN faces ON faces.id = descriptions.face_id
				JOIN general ON general.id = descriptions.general_id'
			),
			$selection->criteria([])
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO(
					$this->database,
					$demand,
					[
						'demands',
						'descriptions',
						'bodies',
						'faces',
						'general',
					]
				)
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