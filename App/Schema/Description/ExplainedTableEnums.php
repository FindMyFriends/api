<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Description;

use FindMyFriends\Schema;
use Predis;

final class ExplainedTableEnums implements Schema\Enum {
	/** @var \PDO */
	private $database;

	/** @var \Predis\ClientInterface */
	private $redis;

	public function __construct(\PDO $database, Predis\ClientInterface $redis) {
		$this->database = $database;
		$this->redis = $redis;
	}

	public function values(): array {
		return [
			'definitions' => [
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
					new Schema\PostgresConstant('breast_sizes', $this->database),
					$this->redis,
					'breast_sizes',
					'constant'
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
				'length' => (new Schema\CachedEnum(
					new Schema\TableEnum('hair_lengths', $this->database),
					$this->redis,
					'hair_lengths',
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
				'length' => (new Schema\CachedEnum(
					new Schema\TableEnum('beard_lengths', $this->database),
					$this->redis,
					'beard_lengths',
					'table'
				))->values(),
				'style' => (new Schema\CachedEnum(
					new Schema\TableEnum('beard_styles', $this->database),
					$this->redis,
					'beard_styles',
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
					'length' => (new Schema\CachedEnum(
						new Schema\TableEnum('nail_lengths', $this->database),
						$this->redis,
						'nail_lengths',
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
				'sex' => (new Schema\CachedEnum(
					new Schema\PostgresConstant('sex', $this->database),
					$this->redis,
					'sex',
					'constant'
				))->values(),
			],
		];
	}
}
