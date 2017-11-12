<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Stored change
 */
final class StoredChange implements Change {
	private $id;
	private $database;

	public function __construct(int $id, \PDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function affect(array $changes): void {
		(new Storage\Transaction($this->database))->start(function() use ($changes): void {
			[
				'general_id' => $general,
				'body_id' => $body,
				'face_id' => $face,
				'seeker_id' => $seeker,
			] = $this->parts($this->id);
			(new Storage\FlatParameterizedQuery(
				$this->database,
				'WITH updated_general AS (
					UPDATE general
					SET gender = :gender,
						race = :race,
						birth_year = to_range(:birth_year_from::INTEGER, :birth_year_to::INTEGER),
						firstname = :firstname,
						lastname = :lastname
					WHERE id = :id
					RETURNING birth_year
				)
				UPDATE general
				SET birth_year = (SELECT birth_year FROM updated_general)
				WHERE id IN (
					SELECT general_id
					FROM base_evolution
					WHERE seeker_id = :seeker_id
				)',
				['id' => $general, 'seeker_id' => $seeker] + $changes['general']
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
				['id' => $face] + $changes['face']
			))->execute();
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE bodies
				SET build = :build,
					skin = :skin,
					weight = :weight,
					height = :height
				WHERE id = :id',
				['id' => $body] + $changes['body']
			))->execute();
		});
	}

	public function print(Output\Format $format): Output\Format {
		$evolution = (new Storage\TypedQuery(
			$this->database,
			new Storage\ParameterizedQuery(
				$this->database,
				'SELECT id, evolved_at,
					build, skin, weight, height,
					acne, beard, complexion, eyebrow, freckles, hair, left_eye, right_eye, shape, teeth,
					age, firstname, lastname, gender, race
					FROM collective_evolutions
					WHERE id = ?',
				[$this->id]
			),
			[
				'hair' => 'hair',
				'left_eye' => 'eye',
				'right_eye' => 'eye',
				'teeth' => 'tooth',
				'age' => 'hstore',
			]
		))->row();
		return new Output\FilledFormat(
			$format,
			[
				'id' => $evolution['id'],
				'evolved_at' => $evolution['evolved_at'],
				'general' => [
					'age' => $evolution['age'],
					'firstname' => $evolution['firstname'],
					'lastname' => $evolution['lastname'],
					'gender' => $evolution['gender'],
					'race' => $evolution['race'],
				],
				'face' => [
					'acne' => $evolution['acne'],
					'beard' => $evolution['beard'],
					'complexion' => $evolution['complexion'],
					'eyebrow' => $evolution['eyebrow'],
					'freckles' => $evolution['freckles'],
					'hair' => $evolution['hair'],
					'eye' => [
						'left' => $evolution['left_eye'],
						'right' => $evolution['right_eye'],
					],
					'shape' => $evolution['shape'],
					'teeth' => $evolution['teeth'],
				],
				'body' => [
					'build' => $evolution['build'],
					'skin' => $evolution['skin'],
					'weight' => $evolution['weight'],
					'height' => $evolution['height'],
				],
			]
		);
	}


	/**
	 * Description parts belonging to the evolution
	 * @param int $evolution
	 * @return array
	 */
	private function parts(int $evolution): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT general_id, body_id, face_id, seeker_id
			FROM descriptions
			JOIN evolutions ON evolutions.description_id = descriptions.id
			WHERE evolutions.id = ?',
			[$evolution]
		))->row();
	}
}