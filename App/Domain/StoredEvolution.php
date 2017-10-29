<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Change in evolution chain
 */
final class StoredEvolution implements Evolution {
	private $id;
	private $database;

	public function __construct(int $id, \PDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$evolution = (new Storage\TypedQuery(
			$this->database,
			new Storage\ParameterizedQuery(
				$this->database,
				'SELECT evolutions.id, evolutions.evolved_at,
				bodies.build, bodies.skin, bodies.weight, bodies.height,
				faces.acne, faces.beard, faces.complexion, faces.eyebrow, faces.freckles, faces.hair, faces.left_eye, faces.right_eye, faces.shape, faces.teeth,
				general.birth_year, general.firstname, general.lastname, general.gender, general.race
				FROM evolutions
				JOIN descriptions ON descriptions.id = evolutions.description_id
				JOIN bodies ON bodies.id = descriptions.body_id
				JOIN faces ON faces.id = descriptions.face_id
				JOIN general ON general.id = descriptions.general_id
				WHERE evolutions.id = ?',
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
				'id' => $evolution['id'],
				'evolved_at' => $evolution['evolved_at'],
				'general' => [
					'birth_year' => $evolution['birth_year'],
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
}