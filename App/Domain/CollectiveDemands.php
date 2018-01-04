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

	public function __construct(Demands $origin, Storage\MetaPDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function ask(array $description): Demand {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\TypedQuery(
			$this->database,
			$selection->expression(
				'SELECT general_age,
				general_firstname,
				general_lastname,
				general_gender,
				general_race,
				hair_style,
				hair_color,
				hair_length,
				hair_highlights,
				hair_roots,
				hair_nature,
				face_care,
				beard_length,
				beard_style,
				beard_color,
				eyebrow_care,
				eyebrow_color,
				face_freckles,
				left_eye_color,
				left_eye_lenses,
				right_eye_color,
				right_eye_lenses,
				face_shape,
				tooth_care,
				tooth_braces,
				body_build,
				body_skin_color,
				body_weight,
				body_height,
				hands_nails_length,
				hands_nails_care,
				hands_nails_color,
				hands_vein_visibility,
				hands_joint_visibility,
				hands_care,
				hands_hair_color,
				hands_hair_amount,
				seeker_id,
				id,
				created_at,
				location_coordinates,
				location_met_at
				FROM collective_demands'
			),
			$selection->criteria([])
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO($this->database, $demand)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\NativeQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM demands'),
			$selection->criteria([])
		))->field();
	}
}