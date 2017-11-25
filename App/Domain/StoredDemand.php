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
			new Storage\ParameterizedQuery(
				$this->database,
				'SELECT id, seeker_id, created_at,
					body_build, skin, weight, height,
					acne, beard, face_complexion, eyebrow, face_freckles, hair, left_eye, right_eye, face_shape, teeth,
					age, firstname, lastname, gender, race,
					location_coordinates, met_at,
					nails, hands_care, hands_veins, hands_joint, hands_hair
					FROM collective_demands
				WHERE id = ?',
				[$this->id]
			),
			[
				'hair' => 'hair',
				'left_eye' => 'eye',
				'right_eye' => 'eye',
				'teeth' => 'tooth',
				'location_coordinates' => 'point',
				'age' => 'hstore',
				'met_at' => 'hstore',
				'nails' => 'nail',
			]
		))->row();
		return new Output\FilledFormat(
			$format,
			[
				'id' => $demand['id'],
				'seeker_id' => $demand['seeker_id'],
				'created_at' => $demand['created_at'],
				'general' => [
					'age' => $demand['age'],
					'firstname' => $demand['firstname'],
					'lastname' => $demand['lastname'],
					'gender' => $demand['gender'],
					'race' => $demand['race'],
				],
				'face' => [
					'acne' => $demand['acne'],
					'beard' => $demand['beard'],
					'complexion' => $demand['face_complexion'],
					'eyebrow' => $demand['eyebrow'],
					'freckles' => $demand['face_freckles'],
					'hair' => $demand['hair'],
					'eye' => [
						'left' => $demand['left_eye'],
						'right' => $demand['right_eye'],
					],
					'shape' => $demand['face_shape'],
					'teeth' => $demand['teeth'],
				],
				'body' => [
					'build' => $demand['body_build'],
					'skin' => $demand['skin'],
					'weight' => $demand['weight'],
					'height' => $demand['height'],
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
						'length' => $demand['nails']['length'],
						'care' => $demand['nails']['care'],
						'color' => $demand['nails']['color'],
					],
					'veins' => $demand['hands_veins'],
					'joint' => $demand['hands_joint'],
					'care' => $demand['hands_care'],
					'hair' => $demand['hands_hair'],
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
		(new Storage\Transaction($this->database))->start(function() use ($description): void {
			[
				'general_id' => $general,
				'body_id' => $body,
				'face_id' => $face,
				'location_id' => $location,
				'hands_id' => $hands,
			] = $this->parts($this->id);
			(new Storage\FlatParameterizedQuery(
				$this->database,
				'UPDATE general
				SET gender = :gender,
					race = :race,
					birth_year = to_range(:birth_year_from::INTEGER, :birth_year_to::INTEGER),
					firstname = :firstname,
					lastname = :lastname
				WHERE id = :id',
				['id' => $general] + $description['general']
			))->execute();
			(new Storage\FlatParameterizedQuery(
				$this->database,
				'UPDATE faces
				SET teeth = ROW(:teeth_care, :teeth_braces)::tooth,
					freckles = :freckles,
					complexion = :complexion,
					beard = :beard,
					acne = :acne,
					shape = :shape,
					hair = ROW(
						:hair_style,
						:hair_color,
						:hair_length,
						:hair_highlights,
						:hair_roots,
						:hair_nature
					)::hair,
					eyebrow = :eyebrow,
					left_eye = ROW(:eye_left_color, :eye_left_lenses)::eye,
					right_eye = ROW(:eye_right_color, :eye_right_lenses)::eye
				WHERE id = :id',
				['id' => $face] + $description['face']
			))->execute();
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE bodies
				SET build = :build,
					skin = :skin,
					weight = :weight,
					height = :height
				WHERE id = :id',
				['id' => $body] + $description['body']
			))->execute();
			(new Storage\FlatParameterizedQuery(
				$this->database,
				'UPDATE locations
				SET coordinates = POINT(:coordinates_latitude, :coordinates_longitude),
					met_at = to_range(:met_at_from::TIMESTAMPTZ, :met_at_to::TIMESTAMPTZ) 
				WHERE id = :id',
				['id' => $location] + $description['location']
			))->execute();
			(new Storage\FlatParameterizedQuery(
				$this->database,
				'UPDATE hands
				SET nails = ROW(:nails_color, :nails_length, :nails_care)::nail,
					care = :care,
					veins = :veins,
					joint = :joint,
					hair = :hair
				WHERE id = :id',
				['id' => $hands] + $description['hands']
			))->execute();
		});
	}

	/**
	 * Description parts belonging to the demand
	 * @param int $demand
	 * @return array
	 */
	private function parts(int $demand): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT general_id, body_id, face_id, location_id, hands_id
			FROM descriptions
			JOIN demands ON demands.description_id = descriptions.id
			WHERE demands.id = ?',
			[$demand]
		))->row();
	}
}