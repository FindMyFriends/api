<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Description;

use FindMyFriends\Schema;

final class ExplainedTableEnums implements Schema\Enum {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function values(): array {
		return [
			'definitions' => [
				'length_unit' => (new Schema\PostgresEnum('length_units', $this->database))->values(),
				'mass_unit' => (new Schema\PostgresEnum('mass_units', $this->database))->values(),
				'eye' => [
					'color' => (new Schema\ColorEnum('eye_colors', $this->database))->values(),
				],
			],
			'body' => [
				'build' => (new Schema\TableEnum('body_builds', $this->database))->values(),
				'breast_size' => (new Schema\PostgresEnum('breast_sizes', $this->database))->values(),
			],
			'hair' => [
				'color' => (new Schema\ColorEnum('hair_colors', $this->database))->values(),
				'style' => (new Schema\TableEnum('hair_styles', $this->database))->values(),
			],
			'beard' => [
				'color' => (new Schema\ColorEnum('beard_colors', $this->database))->values(),
			],
			'eyebrow' => [
				'color' => (new Schema\ColorEnum('eyebrow_colors', $this->database))->values(),
			],
			'hands' => [
				'nails' => [
					'color' => (new Schema\ColorEnum('nail_colors', $this->database))->values(),
				],
				'hair' => [
					'color' => (new Schema\ColorEnum('hand_hair_colors', $this->database))->values(),
				],
			],
			'face' => [
				'shape' => (new Schema\TableEnum('face_shapes', $this->database))->values(),
			],
			'general' => [
				'ethnic_group' => (new Schema\TableEnum('ethnic_groups', $this->database))->values(),
				'gender' => (new Schema\PostgresEnum('genders', $this->database))->values(),
			],
		];
	}
}