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

	public function __construct(Access\User $seeker, \PDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function extend(array $progress): Change {
		$id = (new Storage\FlatParameterizedQuery(
			$this->database,
			'INSERT INTO collective_evolutions (
				evolved_at,
				seeker_id,
				general_race_id,
				general_firstname,
				general_lastname,
				general_gender,
				body,
				hands_nails,
				face_freckles,
				face_care,
				face_beard,
				face_eyebrow,
				face_shape,
				face_tooth,
				face_left_eye,
				face_right_eye,
				hands_vein_visibility,
				hands_joint_visibility,
				hands_care,
				hands_hair,
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
				ROW(NULL, :body_build_id, :body_skin_color_id, :body_weight, :body_height)::bodies,
				ROW(NULL, :hands_nails_color_id, :hands_nails_length, :hands_nails_care)::nails,
				:face_freckles,
				:face_care,
				ROW(NULL, :face_beard_color_id, :face_beard_length, :face_beard_style)::beards,
				ROW(NULL, :face_eyebrow_color_id, :face_eyebrow_care)::eyebrows,
				:face_shape,
				ROW(NULL, :face_teeth_care, :face_teeth_braces)::teeth,
				ROW(NULL, :face_eye_left_color_id, :face_eye_left_lenses)::eyes,
				ROW(NULL, :face_eye_right_color_id, :face_eye_right_lenses)::eyes,
				:hands_vein_visibility,
				:hands_joint_visibility,
				:hands_care,
				ROW(NULL, :hands_hair_color_id, :hands_hair_amount)::hand_hair,
				:hair_color_id,
				:hair_style,
				:hair_length,
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
		$evolutions = (new Query(
			$this->database,
			$selection->expression('SELECT * FROM collective_evolutions WHERE seeker_id = ?'),
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
		return (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM evolutions WHERE seeker_id = ?'),
			$selection->criteria([$this->seeker->id()])
		))->field();
	}
}