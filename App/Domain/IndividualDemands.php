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
					general.birth_year, general.firstname, general.lastname, general.gender, general.race
					FROM demands
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
		['general' => $general, 'body' => $body, 'face' => $face] = $description;
		$id = (new Storage\ParameterizedQuery(
			$this->database,
			'WITH inserted_general AS (
				INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES (?, ?, ?, ?, ?)
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
			INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
				?,
				(SELECT id FROM inserted_description),
				NOW()::TIMESTAMPTZ
			)
			RETURNING id',
			[
				$general['gender'],
				$general['race'],
				$general['birth_year'],
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
			]
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