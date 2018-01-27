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
				'length_unit' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('length_units', $this->database),
					'length_units',
					'enum'
				))->values(),
				'mass_unit' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('mass_units', $this->database),
					'mass_units',
					'enum'
				))->values(),
				'eye' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('eye_colors', $this->database),
						'eye_colors',
						'table'
					))->values(),
				],
			],
			'body' => [
				'build' => (new Schema\CachedEnum(
					new Schema\TableEnum('body_builds', $this->database),
					'body_builds',
					'table'
				))->values(),
				'breast_size' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('breast_sizes', $this->database),
					'breast_sizes',
					'enum'
				))->values(),
			],
			'hair' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('hair_colors', $this->database),
					'hair_colors',
					'table'
				))->values(),
				'style' => (new Schema\CachedEnum(
					new Schema\TableEnum('hair_styles', $this->database),
					'hair_styles',
					'table'
				))->values(),
			],
			'beard' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('beard_colors', $this->database),
					'beard_colors',
					'table'
				))->values(),
			],
			'eyebrow' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('eyebrow_colors', $this->database),
					'eyebrow_colors',
					'table'
				))->values(),
			],
			'hands' => [
				'nails' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('nail_colors', $this->database),
						'nail_colors',
						'table'
					))->values(),
				],
				'hair' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('hand_hair_colors', $this->database),
						'hand_hair_colors',
						'table'
					))->values(),
				],
			],
			'face' => [
				'shape' => (new Schema\CachedEnum(
					new Schema\TableEnum('face_shapes', $this->database),
					'face_shapes',
					'table'
				))->values(),
			],
			'general' => [
				'ethnic_group' => (new Schema\CachedEnum(
					new Schema\TableEnum('ethnic_groups', $this->database),
					'ethnic_groups',
					'table'
				))->values(),
				'gender' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('genders', $this->database),
					'genders',
					'enum'
				))->values(),
			],
		];
	}
}