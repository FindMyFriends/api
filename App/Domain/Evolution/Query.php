<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Storage;

final class Query implements Storage\Query {
	private const TYPES = [
		'face_eyebrow' => 'eyebrows',
		'age' => 'hstore',
		'body' => 'bodies',
		'body_build' => 'body_builds',
		'body_skin_color' => 'colors',
		'face_beard' => 'beards',
		'face_left_eye' => 'eyes',
		'face_right_eye' => 'eyes',
		'face_tooth' => 'teeth',
		'hand_nail_color' => 'colors',
		'face_right_eye_color' => 'colors',
		'face_left_eye_color' => 'colors',
		'hand_hair_color' => 'colors',
		'face_beard_color' => 'colors',
		'face_eyebrow_color' => 'colors',
		'hair_color' => 'colors',
		'general_race' => 'races',
		'hands_nails' => 'nails',
	];
	private $database;
	private $query;
	private $parameters;

	public function __construct(\PDO $database, string $query, array $parameters = []) {
		$this->database = $database;
		$this->query = $query;
		$this->parameters = $parameters;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return $this->origin($this->query, $this->parameters)->field();
	}

	public function row(): array {
		return $this->origin($this->query, $this->parameters)->row();
	}

	public function rows(): array {
		return $this->origin($this->query, $this->parameters)->rows();
	}

	public function execute(): \PDOStatement {
		return $this->origin($this->query, $this->parameters)->execute();
	}

	private function origin(string $query, array $parameters): Storage\Query {
		return new Storage\TypedQuery(
			$this->database,
			new Storage\ParameterizedQuery($this->database, $query, $parameters),
			self::TYPES
		);
	}
}