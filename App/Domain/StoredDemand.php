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
		$demand = (new Storage\TypedQuery(
			$this->database,
			'SELECT * FROM collective_demands WHERE id = ?',
			[$this->id]
		))->row();
		return (new DescriptionFormat($format, $demand))
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
					'met_at' => [
						'from' => $demand['location_met_at'][0],
						'to' => $demand['location_met_at'][1],
					],
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
			SET general_gender = :general_gender,
				general_race_id = :general_race_id,
				general_age = int4range(:general_age_from, :general_age_to),
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
				face_tooth = ROW(NULL, :face_teeth_care, :face_teeth_braces)::teeth,
				location_coordinates = POINT(:location_coordinates_latitude, :location_coordinates_longitude),
				location_met_at = tstzrange(:location_met_at_from, :location_met_at_to),
				hands_nails = ROW(NULL, :hands_nails_color_id, ROW(:hands_nails_length_value, :hands_nails_length_unit), :hands_nails_care),
				hands_vein_visibility = :hands_vein_visibility,
				hands_joint_visibility = :hands_joint_visibility,
				hands_care = :hands_care,
				hands_hair = ROW(NULL, :hands_hair_color_id, :hands_hair_amount)
			WHERE id = :id',
			['id' => $this->id] + $description
		))->execute();
	}
}