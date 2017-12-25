<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Stored change
 */
final class StoredChange implements Change {
	private $id;
	private $database;

	public function __construct(int $id, \PDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function affect(array $changes): void {
		(new Storage\FlatQuery(
			$this->database,
			'UPDATE collective_evolutions
			SET general_race_id = :general_race_id,
				general_firstname = :general_firstname,
				general_lastname = :general_lastname,
				general_gender = :general_gender,
				body_build_id = :body_build_id,
				body_skin_color_id = :body_skin_color_id,
				body_weight = :body_weight,
				body_height = :body_height,
				hands_nails_color_id = :hands_nails_color_id,
				hands_nails_length = ROW(:hands_nails_length_value, :hands_nails_length_unit),
				hands_nails_care = :hands_nails_care,
				face_freckles = :face_freckles,
				face_care = :face_care,
				face_beard_color_id = :face_beard_color_id,
				face_beard_length = ROW(:face_beard_length_value, :face_beard_length_unit),
				face_beard_style = :face_beard_style,
				face_eyebrow_color_id = :face_eyebrow_color_id,
				face_eyebrow_care = :face_eyebrow_care,
				face_shape = :face_shape,
				face_tooth_care = :face_teeth_care,
				face_tooth_braces = :face_teeth_braces,
				face_left_eye_color_id = :face_eye_left_color_id,
				face_left_eye_lenses = :face_eye_left_lenses,
				face_right_eye_color_id = :face_eye_right_color_id,
				face_right_eye_lenses = :face_eye_right_lenses,
				hands_vein_visibility = :hands_vein_visibility,
				hands_joint_visibility = :hands_joint_visibility,
				hands_care = :hands_care,
				hands_hair_color_id = :hands_hair_color_id,
				hands_hair_amount = :hands_hair_amount,
				hair_color_id = :hair_color_id,
				hair_style = :hair_style,
				hair_length = ROW(:hair_length_value, :hair_length_unit),
				hair_highlights = :hair_highlights,
				hair_roots = :hair_roots,
				hair_nature = :hair_nature,
				evolved_at = :evolved_at
			WHERE id = :id',
			['id' => $this->id] + $changes
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$evolution = (new Storage\TypedQuery(
			$this->database,
			'SELECT * FROM collective_evolutions WHERE id = ?',
			[$this->id]
		))->row();
		return (new Domain\CompleteDescription($format, $evolution))
			->with('id', $evolution['id'])
			->with('evolved_at', $evolution['evolved_at']);
	}

	public function revert(): void {
		(new Storage\ApplicationQuery(
			new Storage\NativeQuery(
				$this->database,
				'DELETE FROM evolutions WHERE id = ?',
				[$this->id]
			)
		))->execute();
	}
}