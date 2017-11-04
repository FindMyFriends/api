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
			new Storage\ParameterizedQuery(
				$this->database,
				$selection->expression(
					'SELECT demands.id, demands.seeker_id, demands.created_at,
					bodies.build, bodies.skin, bodies.weight, bodies.height,
					faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
					general.birth_year, general.firstname, general.lastname, general.gender, general.race,
					locations.coordinates, locations.met_at
					FROM demands
					JOIN locations ON locations.id = demands.location_id
					JOIN descriptions ON descriptions.id = demands.description_id
					JOIN bodies ON bodies.id = descriptions.body_id
					JOIN faces ON faces.id = descriptions.face_id
					JOIN general ON general.id = descriptions.general_id
					WHERE demands.seeker_id = ?'
				),
				$selection->criteria([$this->seeker->id()])
			),
			[
				'hair' => 'hair',
				'left_eye' => 'eye',
				'right_eye' => 'eye',
				'teeth' => 'tooth',
				'coordinates' => 'point',
			]
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO($this->database, $demand)
			);
		}
	}

	public function ask(array $description): Demand {
		$id = (new Storage\FlatParameterizedQuery(
			$this->database,
			'WITH inserted_general AS (
				INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES (
					:general_gender,
					:general_race,
					:general_birth_year,
					:general_firstname,
					:general_lastname
				)
				RETURNING id
			), inserted_face AS (
				INSERT INTO faces (teeth, freckles, complexion, beard, acne, shape, hair, eyebrow, left_eye, right_eye) VALUES (
					ROW(:face_teeth_care, :face_teeth_braces)::tooth,
					:face_freckles,
					:face_complexion,
					:face_beard,
					:face_acne,
					:face_shape,
					ROW(
						:face_hair_style,
						:face_hair_color,
						:face_hair_length,
						:face_hair_highlights,
						:face_hair_roots,
						:face_hair_nature
					)::hair,
					:face_eyebrow,
					ROW(:face_eye_left_color, :face_eye_left_lenses)::eye,
					ROW(:face_eye_right_color, :face_eye_right_lenses)::eye
				)
				RETURNING id
			),
			inserted_body AS (
				INSERT INTO bodies (build, skin, weight, height) VALUES (
					:body_build,
					:body_skin,
					:body_weight,
					:body_height
				)
				RETURNING id
			),
			inserted_description AS (
				INSERT INTO descriptions (general_id, body_id, face_id) VALUES (
					(SELECT id FROM inserted_general),
					(SELECT id FROM inserted_body),
					(SELECT id FROM inserted_face)
				)
				RETURNING id
			),
			inserted_location AS (
				INSERT INTO locations (coordinates, met_at) VALUES (
					POINT(:location_coordinates_latitude, :location_coordinates_longitude),
					:location_met_at
				)
				RETURNING id
			)
			INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
				:seeker,
				(SELECT id FROM inserted_description),
				NOW()::TIMESTAMPTZ,
				(SELECT id FROM inserted_location)
			)
			RETURNING id',
			['seeker' => $this->seeker->id()] + $description
		))->field();
		return new StoredDemand($id, $this->database);
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM demands WHERE seeker_id = ?'),
			$selection->criteria([$this->seeker->id()])
		))->field();
	}
}