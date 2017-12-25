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

	public function __construct(Access\User $seeker, \PDO $database) {
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
				face_beard_color_id,
				face_beard_length,
				face_beard_style,
				face_eyebrow_color_id,
				face_eyebrow_care,
				face_shape,
				face_tooth_care,
				face_tooth_braces,
				face_left_eye_color_id,
				face_left_eye_lenses,
				face_right_eye_color_id,
				face_right_eye_lenses,
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
				tstzrange(:location_met_at_from::TIMESTAMPTZ, :location_met_at_to::TIMESTAMPTZ),
				:seeker,
				int4range(:general_age_from, :general_age_to),
				:general_race_id,
				:general_firstname,
				:general_lastname,
				:general_gender,
				:body_build_id,
				:body_skin_color_id,
				:body_weight,
				:body_height,
				:hands_nails_color_id,
				ROW(:hands_nails_length_value, :hands_nails_length_unit),
				:hands_nails_care,
				POINT(:location_coordinates_latitude, :location_coordinates_longitude),
				:face_freckles,
				:face_care,
				:face_beard_color_id,
				ROW(:face_beard_length_value, :face_beard_length_unit),
				:face_beard_style,
				:face_eyebrow_color_id,
				:face_eyebrow_care,
				:face_shape,
				:face_teeth_care,
				:face_teeth_braces,
				:face_eye_left_color_id,
				:face_eye_left_lenses,
				:face_eye_right_color_id,
				:face_eye_right_lenses,
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