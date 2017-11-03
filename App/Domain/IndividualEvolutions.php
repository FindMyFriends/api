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
		['general' => $general, 'body' => $body, 'face' => $face] = $progress;
		$id = (new Storage\ParameterizedQuery(
			$this->database,
			'WITH inserted_general AS (
				INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES (
					?,
					?,
					(
						SELECT birth_year
						FROM general
						JOIN descriptions ON descriptions.general_id = general.id
						JOIN evolutions ON evolutions.description_id = descriptions.id
						WHERE evolutions.seeker_id = ?
						LIMIT 1
					),
					?,
					?
				)
				RETURNING id
			), inserted_face AS (
				INSERT INTO faces (teeth, freckles, complexion, beard, acne, shape, hair, eyebrow, left_eye, right_eye) VALUES (
					ROW(?, ?)::tooth,
					?,
					?,
					?,
					?,
					?,
					ROW(?, ?, ?, ?, ?, ?)::hair,
					?,
					ROW(?, ?)::eye,
					ROW(?, ?)::eye
				)
				RETURNING id
			),
			inserted_body AS (
				INSERT INTO bodies (build, skin, weight, height) VALUES (?, ?, ?, ?)
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
				?,
				?
			)
			RETURNING id',
			[
				$general['gender'],
				$general['race'],
				$this->seeker->id(),
				$general['firstname'],
				$general['lastname'],
				$face['teeth']['care'],
				$face['teeth']['braces'],
				$face['freckles'],
				$face['complexion'],
				$face['beard'],
				$face['acne'],
				$face['shape'],
				$face['hair']['style'],
				$face['hair']['color'],
				$face['hair']['length'],
				$face['hair']['highlights'],
				$face['hair']['roots'],
				$face['hair']['nature'],
				$face['eyebrow'],
				$face['eye']['left']['color'],
				$face['eye']['left']['lenses'],
				$face['eye']['right']['color'],
				$face['eye']['right']['lenses'],
				$body['build'],
				$body['skin'],
				$body['weight'],
				$body['height'],
				$this->seeker->id(),
				$progress['evolved_at'],
			]
		))->field();
		return new StoredEvolution($id, $this->database);
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		$evolutions = (new Storage\TypedQuery(
			$this->database,
			new Storage\ParameterizedQuery(
				$this->database,
				$selection->expression(
					'SELECT evolutions.id, evolutions.evolved_at,
					bodies.build, bodies.skin, bodies.weight, bodies.height,
					faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
					general.birth_year, general.firstname, general.lastname, general.gender, general.race
					FROM evolutions
					JOIN descriptions ON descriptions.id = evolutions.description_id
					JOIN bodies ON bodies.id = descriptions.body_id
					JOIN faces ON faces.id = descriptions.face_id
					JOIN general ON general.id = descriptions.general_id
					WHERE evolutions.seeker_id = ?'
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