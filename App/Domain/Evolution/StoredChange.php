<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

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
				body = ROW(NULL, :body_build_id, :body_skin_color_id, :body_weight, :body_height)::bodies,
				face_left_eye = ROW(NULL, :face_eye_left_color_id, :face_eye_left_lenses)::eyes,
				face_right_eye = ROW(NULL, :face_eye_right_color_id, :face_eye_right_lenses)::eyes,
				face_eyebrow = ROW(NULL, :face_eyebrow_color_id, :face_eyebrow_care)::eyebrows,
				face_beard = ROW(NULL, :face_beard_color_id, ROW(:face_beard_length_value, :face_beard_length_unit), :face_beard_style)::beards,
				face_tooth = ROW(NULL, :face_teeth_care, :face_teeth_braces)::teeth,
				hands_nails = ROW(NULL, :hands_nails_color_id, ROW(:hands_nails_length_value, :hands_nails_length_unit), :hands_nails_care)::nails,
				hands_vein_visibility = :hands_vein_visibility,
				hands_joint_visibility = :hands_joint_visibility,
				hands_care = :hands_care,
				hands_hair = ROW(NULL, :hands_hair_color_id, :hands_hair_amount)::hand_hair,
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
		return new Output\FilledFormat(
			$format,
			[
				'id' => $evolution['id'],
				'evolved_at' => $evolution['evolved_at'],
				'general' => [
					'age' => [
						'from' => $evolution['general_age'][0],
						'to' => $evolution['general_age'][1],
					],
					'firstname' => $evolution['general_firstname'],
					'lastname' => $evolution['general_lastname'],
					'gender' => $evolution['general_gender'],
					'race' => $evolution['general_race'],
				],
				'hair' => [
					'style' => $evolution['hair_style'],
					'color' => $evolution['hair_color'],
					'length' => $evolution['hair_length'],
					'highlights' => $evolution['hair_highlights'],
					'roots' => $evolution['hair_roots'],
					'nature' => $evolution['hair_nature'],
				],
				'face' => [
					'care' => $evolution['face_care'],
					'beard' => [
						'id' => $evolution['face_beard']['id'],
						'length' => $evolution['face_beard']['length'],
						'style' => $evolution['face_beard']['style'],
						'color' => $evolution['face_beard_color'],
					],
					'eyebrow' => [
						'id' => $evolution['face_eyebrow']['id'],
						'care' => $evolution['face_eyebrow']['care'],
						'color' => $evolution['face_eyebrow_color'],
					],
					'freckles' => $evolution['face_freckles'],
					'eye' => [
						'left' => [
							'id' => $evolution['face_left_eye']['id'],
							'color' => $evolution['face_left_eye_color'],
							'lenses' => $evolution['face_left_eye']['lenses'],
						],
						'right' => [
							'id' => $evolution['face_right_eye']['id'],
							'color' => $evolution['face_right_eye_color'],
							'lenses' => $evolution['face_right_eye']['lenses'],
						],
					],
					'shape' => $evolution['face_shape'],
					'teeth' => $evolution['face_tooth'],
				],
				'body' => [
					'build' => $evolution['body_build'],
					'skin_color' => $evolution['body_skin_color'],
					'weight' => $evolution['body']['weight'],
					'height' => $evolution['body']['height'],
				],
				'hands' => [
					'nails' => [
						'length' => $evolution['hands_nails']['length'],
						'care' => $evolution['hands_nails']['care'],
						'color' => $evolution['hand_nail_color'],
					],
					'vein_visibility' => $evolution['hands_vein_visibility'],
					'joint_visibility' => $evolution['hands_joint_visibility'],
					'care' => $evolution['hands_care'],
					'hair' => [
						'color' => $evolution['hand_hair_color'],
						'amount' => $evolution['hand_hair_amount'],
					],
				],
			]
		);
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