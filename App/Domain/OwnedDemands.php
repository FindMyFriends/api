<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Demands belonging to the seeker
 */
final class OwnedDemands implements Demands {
	private $seeker;
	private $database;

	public function __construct(Access\User $seeker, \PDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$demands = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT demands.id, demands.seeker_id, demands.created_at,
				bodies.build, bodies.skin, bodies.weight, bodies.height,
				faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
				general.age, general.firstname, general.lastname, general.gender, general.race
				FROM demands
				JOIN descriptions ON descriptions.id = demands.description_id
				JOIN bodies ON bodies.id = descriptions.body_id
				JOIN faces ON faces.id = descriptions.face_id
				JOIN general ON general.id = descriptions.general_id
				WHERE demands.seeker_id = ?'
			),
			$selection->criteria([$this->seeker->id()])
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO(
					$this->database,
					$demand,
					[
						'demands',
						'descriptions',
						'bodies',
						'faces',
						'general',
					]
				)
			);
		}
	}

	public function ask(array $description): Demand {
		$id = (new Storage\FlatParameterizedQuery(
			$this->database,
			'WITH inserted_general AS (
				INSERT INTO general (gender, race, age, firstname, lastname) VALUES (
					:general_gender,
					:general_race,
					:general_age,
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
					ROW(:face_left_eye_color, :face_left_eye_lenses)::eye,
					ROW(:face_right_eye_color, :face_right_eye_lenses)::eye
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
			)
			INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
				:seeker,
				(SELECT id FROM inserted_description),
				NOW()::TIMESTAMPTZ
			)
			RETURNING id',
			['seeker' => $this->seeker->id()] + $description
		))->field();
		return new StoredDemand($id, $this->database);
	}
}