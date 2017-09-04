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
				general.age, general.firstname, general.lastname, general.gender, general.race
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
		return new Output\FilledFormat($format, $demand);
	}

	public function retract(): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM demands WHERE id = ?',
			[$this->id]
		))->execute();
	}
}