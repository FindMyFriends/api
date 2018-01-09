<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Demands belonging to the seeker
 */
final class IndividualDemands implements Demands {
	private $seeker;
	private $database;

	public function __construct(Access\User $seeker, Storage\MetaPDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\TypedQuery(
			$this->database,
			$selection->expression('SELECT * FROM collective_demands WHERE seeker_id = ?'),
			$selection->criteria([$this->seeker->id()])
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO($this->database, $demand)
			);
		}
	}

	public function ask(array $description): Demand {
		$id = (new Storage\FlatQuery(
			$this->database,
			'INSERT INTO collective_demands (
				location_met_at,
				seeker_id,
				general_age,
				general_race_id,
				general_firstname,
				general_lastname,
				general_gender,
				body_build_id,
				body_skin_color_id,
				body_weight,
				body_height,
				hands_nails_color_id,
				hands_nails_length,
				hands_nails_care,
				location_coordinates,
				face_freckles,
				face_care,
				beard_color_id,
				beard_length,
				beard_style,
				eyebrow_color_id,
				eyebrow_care,
				face_shape,
				tooth_care,
				tooth_braces,
				left_eye_color_id,
				left_eye_lenses,
				right_eye_color_id,
				right_eye_lenses,
				hands_vein_visibility,
				hands_joint_visibility,
				hands_care,
				hands_hair_color_id,
				hands_hair_amount,
				hair_color_id,
				hair_style,
				hair_length,
				hair_highlights,
				hair_roots,
				hair_nature
			) VALUES (
				ROW(:location_met_at_moment, :location_met_at_timeline_side, :location_met_at_approximation),
				:seeker,
				int4range(:general_age_from, :general_age_to),
				:general_race_id,
				:general_firstname,
				:general_lastname,
				:general_gender,
				:body_build_id,
				:body_skin_color_id,
				:body_weight,
				ROW(:body_height_value, :body_height_unit),
				:hands_nails_color_id,
				ROW(:hands_nails_length_value, :hands_nails_length_unit),
				:hands_nails_care,
				POINT(:location_coordinates_latitude, :location_coordinates_longitude),
				:face_freckles,
				:face_care,
				:beard_color_id,
				ROW(:beard_length_value, :beard_length_unit),
				:beard_style,
				:eyebrow_color_id,
				:eyebrow_care,
				:face_shape,
				:teeth_care,
				:teeth_braces,
				:eye_left_color_id,
				:eye_left_lenses,
				:eye_right_color_id,
				:eye_right_lenses,
				:hands_vein_visibility,
				:hands_joint_visibility,
				:hands_care,
				:hands_hair_color_id,
				:hands_hair_amount,
				:hair_color_id,
				:hair_style,
				ROW(:hair_length_value, :hair_length_unit),
				:hair_highlights,
				:hair_roots,
				:hair_nature
			)
			RETURNING id',
			['seeker' => $this->seeker->id()] + $description
		))->field();
		return new StoredDemand($id, $this->database);
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\NativeQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM demands WHERE seeker_id = ?'),
			$selection->criteria([$this->seeker->id()])
		))->field();
	}
}