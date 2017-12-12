<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;
use Klapuch\Storage;

final class StoredDemand implements Demand {
	private $id;
	private $database;

	public function __construct(int $id, \PDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$demand = (new Query(
			$this->database,
			'SELECT * FROM collective_demands
				WHERE id = ?',
			[$this->id]
		))->row();
		return new Output\FilledFormat(
			$format,
			[
				'id' => $demand['id'],
				'seeker_id' => $demand['seeker_id'],
				'created_at' => $demand['created_at'],
				'general' => [
					'age' => $demand['age'],
					'firstname' => $demand['general_firstname'],
					'lastname' => $demand['general_lastname'],
					'gender' => $demand['general_gender'],
					'race' => $demand['general_race'],
				],
				'hair' => [
					'style' => $demand['hair_style'],
					'color' => $demand['hair_color'],
					'length' => $demand['hair_length'],
					'highlights' => $demand['hair_highlights'],
					'roots' => $demand['hair_roots'],
					'nature' => $demand['hair_nature'],
				],
				'face' => [
					'care' => $demand['face_care'],
					'beard' => [
						'id' => $demand['face_beard']['id'],
						'length' => $demand['face_beard']['length'],
						'style' => $demand['face_beard']['style'],
						'color' => $demand['face_beard_color'],
					],
					'eyebrow' => [
						'id' => $demand['face_eyebrow']['id'],
						'care' => $demand['face_eyebrow']['care'],
						'color' => $demand['face_eyebrow_color'],
					],
					'freckles' => $demand['face_freckles'],
					'eye' => [
						'left' => [
							'id' => $demand['face_left_eye']['id'],
							'color' => $demand['face_left_eye_color'],
							'lenses' => $demand['face_left_eye']['lenses'],
						],
						'right' => [
							'id' => $demand['face_right_eye']['id'],
							'color' => $demand['face_right_eye_color'],
							'lenses' => $demand['face_right_eye']['lenses'],
						],
					],
					'shape' => $demand['face_shape'],
					'teeth' => $demand['face_tooth'],
				],
				'body' => [
					'build' => $demand['body_build'],
					'skin_color' => $demand['body_skin_color'],
					'weight' => $demand['body']['weight'],
					'height' => $demand['body']['height'],
				],
				'location' => [
					'coordinates' => [
						'latitude' => $demand['location_coordinates']['x'],
						'longitude' => $demand['location_coordinates']['y'],
					],
					'met_at' => $demand['met_at'],
				],
				'hands' => [
					'nails' => [
						'length' => $demand['hands_nails']['length'],
						'care' => $demand['hands_nails']['care'],
						'color' => $demand['hand_nail_color'],
					],
					'vein_visibility' => $demand['hands_vein_visibility'],
					'joint_visibility' => $demand['hands_joint_visibility'],
					'care' => $demand['hands_care'],
					'hair' => [
						'color' => $demand['hand_hair_color'],
						'amount' => $demand['hand_hair_amount'],
					],
				],
			]
		);
	}

	public function retract(): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM demands WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function reconsider(array $description): void {
		(new Storage\FlatParameterizedQuery(
			$this->database,
			'UPDATE collective_demands
			SET general_gender = :general_gender,
				general_race_id = :general_race_id,
				general_birth_year = to_range(:general_birth_year_from::INTEGER, :general_birth_year_to::INTEGER),
				general_firstname = :general_firstname,
				general_lastname = :general_lastname,
				hair_style = :hair_style,
				hair_color_id = :hair_color_id,
				hair_length = :hair_length,
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
				face_beard = ROW(NULL, :face_beard_color_id, :face_beard_length, :face_beard_style)::beards,
				face_tooth = ROW(NULL, :face_teeth_care, :face_teeth_braces)::teeth,
				location_coordinates = POINT(:location_coordinates_latitude, :location_coordinates_longitude),
				location_met_at = to_range(:location_met_at_from::TIMESTAMPTZ, :location_met_at_to::TIMESTAMPTZ),
				hands_nails = ROW(NULL, :hands_nails_color_id, :hands_nails_length, :hands_nails_care)::nails,
				hands_vein_visibility = :hands_vein_visibility,
				hands_joint_visibility = :hands_joint_visibility,
				hands_care = :hands_care,
				hands_hair = ROW(NULL, :hands_hair_color_id, :hands_hair_amount)::hand_hair
			WHERE id = :id',
			['id' => $this->id] + $description
		))->execute();
	}
}