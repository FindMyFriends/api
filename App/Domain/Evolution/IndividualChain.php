<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Chain for one particular seeker
 */
final class IndividualChain implements Chain {
	private $seeker;
	private $database;

	public function __construct(Access\User $seeker, Storage\MetaPDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function extend(array $progress): Change {
		$id = (new Storage\FlatQuery(
			$this->database,
			'INSERT INTO collective_evolutions (
				evolved_at,
				seeker_id,
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
				:evolved_at,
				:seeker,
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
			['seeker' => $this->seeker->id()] + $progress
		))->field();
		return new StoredChange($id, $this->database);
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		$evolutions = (new Storage\TypedQuery(
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
				id,
				evolved_at
				FROM collective_evolutions
				WHERE seeker_id = ?'
			),
			$selection->criteria([$this->seeker->id()])
		))->rows();
		foreach ($evolutions as $change) {
			yield new StoredChange(
				$change['id'],
				new Storage\MemoryPDO($this->database, $change)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\NativeQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM evolutions WHERE seeker_id = ?'),
			$selection->criteria([$this->seeker->id()])
		))->field();
	}
}