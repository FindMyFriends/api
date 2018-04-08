<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Description;

use FindMyFriends\Schema;
use Predis;

final class ExplainedTableEnums implements Schema\Enum {
	private $database;
	private $redis;

	public function __construct(\PDO $database, Predis\ClientInterface $redis) {
		$this->database = $database;
		$this->redis = $redis;
	}

	public function values(): array {
		return [
			'definitions' => [
				'length_unit' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('length_units', $this->database),
					$this->redis,
					'length_units',
					'enum'
				))->values(),
				'mass_unit' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('mass_units', $this->database),
					$this->redis,
					'mass_units',
					'enum'
				))->values(),
				'eye' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('eye_colors', $this->database),
						$this->redis,
						'eye_colors',
						'table'
					))->values(),
				],
			],
			'body' => [
				'build' => (new Schema\CachedEnum(
					new Schema\TableEnum('body_builds', $this->database),
					$this->redis,
					'body_builds',
					'table'
				))->values(),
				'breast_size' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('breast_sizes', $this->database),
					$this->redis,
					'breast_sizes',
					'enum'
				))->values(),
			],
			'hair' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('hair_colors', $this->database),
					$this->redis,
					'hair_colors',
					'table'
				))->values(),
				'style' => (new Schema\CachedEnum(
					new Schema\TableEnum('hair_styles', $this->database),
					$this->redis,
					'hair_styles',
					'table'
				))->values(),
			],
			'beard' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('beard_colors', $this->database),
					$this->redis,
					'beard_colors',
					'table'
				))->values(),
			],
			'eyebrow' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('eyebrow_colors', $this->database),
					$this->redis,
					'eyebrow_colors',
					'table'
				))->values(),
			],
			'hands' => [
				'nails' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('nail_colors', $this->database),
						$this->redis,
						'nail_colors',
						'table'
					))->values(),
				],
				'hair' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('hand_hair_colors', $this->database),
						$this->redis,
						'hand_hair_colors',
						'table'
					))->values(),
				],
			],
			'face' => [
				'shape' => (new Schema\CachedEnum(
					new Schema\TableEnum('face_shapes', $this->database),
					$this->redis,
					'face_shapes',
					'table'
				))->values(),
			],
			'general' => [
				'ethnic_group' => (new Schema\CachedEnum(
					new Schema\TableEnum('ethnic_groups', $this->database),
					$this->redis,
					'ethnic_groups',
					'table'
				))->values(),
				'gender' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('genders', $this->database),
					$this->redis,
					'genders',
					'enum'
				))->values(),
			],
		];
	}
}
