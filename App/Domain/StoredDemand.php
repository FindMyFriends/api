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
				'SELECT demands.id, demands.seeker_id, demands.created_at,
				bodies.build, bodies.skin, bodies.weight, bodies.height,
				faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
				general.birth_year, general.firstname, general.lastname, general.gender, general.race
				FROM demands
				JOIN descriptions ON descriptions.id = demands.description_id
				JOIN bodies ON bodies.id = descriptions.body_id
				JOIN faces ON faces.id = descriptions.face_id
				JOIN general ON general.id = descriptions.general_id
				WHERE demands.id = ?',
				[$this->id]
			),
			[
				'hair' => 'hair',
				'left_eye' => 'eye',
				'right_eye' => 'eye',
				'teeth' => 'tooth',
			]
		))->row();
		return new Output\FilledFormat(
			$format,
			[
				'id' => $demand['id'],
				'seeker_id' => $demand['seeker_id'],
				'created_at' => $demand['created_at'],
				'general' => [
					'birth_year' => $demand['birth_year'],
					'firstname' => $demand['firstname'],
					'lastname' => $demand['lastname'],
					'gender' => $demand['gender'],
					'race' => $demand['race'],
				],
				'face' => [
					'acne' => $demand['acne'],
					'beard' => $demand['beard'],
					'complexion' => $demand['complexion'],
					'eyebrow' => $demand['eyebrow'],
					'freckles' => $demand['freckles'],
					'hair' => $demand['hair'],
					'eye' => [
						'left' => $demand['left_eye'],
						'right' => $demand['right_eye'],
					],
					'shape' => $demand['shape'],
					'teeth' => $demand['teeth'],
				],
				'body' => [
					'build' => $demand['build'],
					'skin' => $demand['skin'],
					'weight' => $demand['weight'],
					'height' => $demand['height'],
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
			$parts = $this->parts($this->id);
			$description['general']['id'] = $parts['general_id'];
			$description['face']['id'] = $parts['face_id'];
			$description['body']['id'] = $parts['body_id'];
			['face' => $face, 'general' => $general, 'body' => $body] = $description;
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE general
				SET gender = ?,
					race = ?,
					birth_year = ?,
					firstname = ?,
					lastname = ?
				WHERE id = ?',
				[
					$general['gender'],
					$general['race'],
					$general['birth_year'],
					$general['firstname'],
					$general['lastname'],
					$general['id'],
				]
			))->execute();
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE faces
				SET teeth = ROW(?, ?)::tooth,
					freckles = ?,
					complexion = ?,
					beard = ?,
					acne = ?,
					shape = ?,
					hair = ROW(?, ?, ?, ?, ?, ?)::hair,
					eyebrow = ?,
					left_eye = ROW(?, ?)::eye,
					right_eye = ROW(?, ?)::eye
				WHERE id = ?',
				[
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
					$face['id'],
				]
			))->execute();
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE bodies
				SET build = ?,
					skin = ?,
					weight = ?,
					height = ?
				WHERE id = ?',
				[
					$body['build'],
					$body['skin'],
					$body['weight'],
					$body['height'],
					$body['id'],
				]
			))->execute();
		});
	}

	/**
	 * Description belonging to the demand
	 * @param int $demand
	 * @return array
	 */
	private function parts(int $demand): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT general_id, body_id, face_id
			FROM descriptions
			WHERE id = (SELECT description_id FROM demands WHERE id = ?)',
			[$demand]
		))->row();
	}
}