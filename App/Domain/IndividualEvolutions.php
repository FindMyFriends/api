<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Evolutions chain for one particular seeker
 */
final class IndividualEvolutions implements Evolutions {
	private $seeker;
	private $database;

	public function __construct(Access\User $seeker, \PDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function evolve(array $progress): Evolution {
		$id = (new Storage\FlatParameterizedQuery(
			$this->database,
			'WITH inserted_general AS (
				INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES (
					:general_gender,
					:general_race,
					(
						SELECT birth_year
						FROM general
						JOIN descriptions ON descriptions.general_id = general.id
						JOIN evolutions ON evolutions.description_id = descriptions.id
						WHERE evolutions.seeker_id = :seeker
						LIMIT 1
					),
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
			)
			INSERT INTO evolutions (description_id, seeker_id, evolved_at) VALUES (
				(SELECT id FROM inserted_description),
				:seeker,
				:evolved_at
			)
			RETURNING id',
			['seeker' => $this->seeker->id()] + $progress
		))->field();
		return new StoredEvolution($id, $this->database);
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		$evolutions = (new Storage\TypedQuery(
			$this->database,
			new Storage\ParameterizedQuery(
				$this->database,
				$selection->expression(
					'SELECT id, evolved_at,
					build, skin, weight, height,
					acne, beard, complexion, eyebrow, freckles, hair, left_eye, right_eye, shape, teeth,
					birth_year, firstname, lastname, gender, race
					FROM collective_evolutions WHERE seeker_id = ?'
				),
				$selection->criteria([$this->seeker->id()])
			),
			[
				'hair' => 'hair',
				'left_eye' => 'eye',
				'right_eye' => 'eye',
				'teeth' => 'tooth',
			]
		))->rows();
		foreach ($evolutions as $change) {
			yield new StoredEvolution(
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