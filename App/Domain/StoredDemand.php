<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Output;
use Klapuch\Storage;

final class StoredDemand implements Demand {
	private $id;
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$demand = (new Storage\TypedQuery(
			$this->database,
			'SELECT general_age,
				general_firstname,
				general_lastname,
				general_gender,
				general_ethnic_group,
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
			FROM collective_demands
			WHERE id = ?
			',
			[$this->id]
		))->row();
		return (new CompleteDescription($format, $demand))
			->with('id', $demand['id'])
			->with('seeker_id', $demand['seeker_id'])
			->with('created_at', $demand['created_at'])
			->with(
				'location',
				[
					'coordinates' => [
						'latitude' => $demand['location_coordinates']['x'],
						'longitude' => $demand['location_coordinates']['y'],
					],
					'met_at' => $demand['location_met_at'],
				]
			);
	}

	public function retract(): void {
		(new Storage\NativeQuery(
			$this->database,
			'DELETE FROM demands WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function reconsider(array $description): void {
		(new Storage\FlatQuery(
			$this->database,
			'UPDATE collective_demands
			SET location_met_at = ROW(:location_met_at_moment, :location_met_at_timeline_side, :location_met_at_approximation),
				general_age = int4range(:general_age_from, :general_age_to),
				general_ethnic_group_id = :general_ethnic_group_id,
				general_firstname = :general_firstname,
				general_lastname = :general_lastname,
				general_gender = :general_gender,
				body_build_id = :body_build_id,
				body_skin_color_id = :body_skin_color_id,
				body_weight = ROW(:body_weight_value, :body_weight_unit),
				body_height = ROW(:body_height_value, :body_height_unit),
				hands_nails_color_id = :hands_nails_color_id,
				hands_nails_length = ROW(:hands_nails_length_value, :hands_nails_length_unit),
				hands_nails_care = :hands_nails_care,
				location_coordinates = POINT(:location_coordinates_latitude, :location_coordinates_longitude),
				face_freckles = :face_freckles,
				face_care = :face_care,
				beard_color_id = :beard_color_id,
				beard_length = ROW(:beard_length_value, :beard_length_unit),
				beard_style = :beard_style,
				eyebrow_color_id = :eyebrow_color_id,
				eyebrow_care = :eyebrow_care,
				face_shape = :face_shape,
				tooth_care = :teeth_care,
				tooth_braces = :teeth_braces,
				left_eye_color_id = :eye_left_color_id,
				left_eye_lenses = :eye_left_lenses,
				right_eye_color_id = :eye_right_color_id,
				right_eye_lenses = :eye_right_lenses,
				hands_vein_visibility = :hands_vein_visibility,
				hands_joint_visibility = :hands_joint_visibility,
				hands_care = :hands_care,
				hands_hair_color_id = :hands_hair_color_id,
				hands_hair_amount = :hands_hair_amount,
				hair_color_id = :hair_color_id,
				hair_style_id = :hair_style_id,
				hair_length = ROW(:hair_length_value, :hair_length_unit),
				hair_highlights = :hair_highlights,
				hair_roots = :hair_roots,
				hair_nature = :hair_nature
			WHERE id = :id',
			['id' => $this->id] + $description
		))->execute();
	}
}