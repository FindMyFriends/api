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
			SET general_gender = :general_gender,
				general_race_id = :general_race_id,
				general_firstname = :general_firstname,
				general_lastname = :general_lastname,
				hair_style = :hair_style,
				hair_color_id = :hair_color_id,
				hair_length = ROW(:hair_length_value, :hair_length_unit),
				hair_highlights = :hair_highlights,
				hair_roots = :hair_roots,
				hair_nature = :hair_nature,
				face_freckles = :face_freckles,
				face_care = :face_care,
				face_shape = :face_shape,
				body = ROW(NULL, :body_build_id, :body_skin_color_id, :body_weight, :body_height),
				face_left_eye = ROW(NULL, :face_eye_left_color_id, :face_eye_left_lenses),
				face_right_eye = ROW(NULL, :face_eye_right_color_id, :face_eye_right_lenses),
				face_eyebrow = ROW(NULL, :face_eyebrow_color_id, :face_eyebrow_care),
				face_beard = ROW(NULL, :face_beard_color_id, ROW(:face_beard_length_value, :face_beard_length_unit), :face_beard_style),
				face_tooth = ROW(NULL, :face_teeth_care, :face_teeth_braces),
				hands_nails = ROW(NULL, :hands_nails_color_id, ROW(:hands_nails_length_value, :hands_nails_length_unit), :hands_nails_care),
				hands_vein_visibility = :hands_vein_visibility,
				hands_joint_visibility = :hands_joint_visibility,
				hands_care = :hands_care,
				hands_hair = ROW(NULL, :hands_hair_color_id, :hands_hair_amount),
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
		return (new Domain\DescriptionFormat($format, $evolution))
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