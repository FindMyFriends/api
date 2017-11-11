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
}