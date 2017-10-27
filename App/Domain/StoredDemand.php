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
			['general_id' => $general, 'body_id' => $body, 'face_id' => $face] = $this->description($this->id);
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE general
				SET gender = :gender,
					race = :race,
					birth_year = :birth_year,
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
		});
	}

	/**
	 * Description belonging to the demand
	 * @param int $demand
	 * @return array
	 */
	private function description(int $demand): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT general_id, body_id, face_id
			FROM descriptions
			WHERE id = (SELECT description_id FROM demands WHERE id = ?)',
			[$demand]
		))->row();
	}
}