<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Description;

use FindMyFriends\Schema;
use Klapuch\Storage;
use Predis;

final class ExplainedTableEnums implements Schema\Enum {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Predis\ClientInterface */
	private $redis;

	public function __construct(Storage\Connection $connection, Predis\ClientInterface $redis) {
		$this->connection = $connection;
		$this->redis = $redis;
	}

	public function values(): array {
		return [
			'definitions' => [
				'eye' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('eye_colors', $this->connection),
						$this->redis,
						'eye_colors',
						'table'
					))->values(),
				],
			],
			'body' => [
				'build' => (new Schema\CachedEnum(
					new Schema\TableEnum('body_builds', $this->connection),
					$this->redis,
					'body_builds',
					'table'
				))->values(),
				'breast_size' => (new Schema\CachedEnum(
					new Schema\PostgresConstant('breast_sizes', $this->connection),
					$this->redis,
					'breast_sizes',
					'constant'
				))->values(),
			],
			'hair' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('hair_colors', $this->connection),
					$this->redis,
					'hair_colors',
					'table'
				))->values(),
				'style' => (new Schema\CachedEnum(
					new Schema\TableEnum('hair_styles', $this->connection),
					$this->redis,
					'hair_styles',
					'table'
				))->values(),
				'length' => (new Schema\CachedEnum(
					new Schema\TableEnum('hair_lengths', $this->connection),
					$this->redis,
					'hair_lengths',
					'table'
				))->values(),
			],
			'beard' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('beard_colors', $this->connection),
					$this->redis,
					'beard_colors',
					'table'
				))->values(),
				'length' => (new Schema\CachedEnum(
					new Schema\TableEnum('beard_lengths', $this->connection),
					$this->redis,
					'beard_lengths',
					'table'
				))->values(),
				'style' => (new Schema\CachedEnum(
					new Schema\TableEnum('beard_styles', $this->connection),
					$this->redis,
					'beard_styles',
					'table'
				))->values(),
			],
			'eyebrow' => [
				'color' => (new Schema\CachedEnum(
					new Schema\ColorEnum('eyebrow_colors', $this->connection),
					$this->redis,
					'eyebrow_colors',
					'table'
				))->values(),
			],
			'hands' => [
				'nails' => [
					'color' => (new Schema\CachedEnum(
						new Schema\ColorEnum('nail_colors', $this->connection),
						$this->redis,
						'nail_colors',
						'table'
					))->values(),
					'length' => (new Schema\CachedEnum(
						new Schema\TableEnum('nail_lengths', $this->connection),
						$this->redis,
						'nail_lengths',
						'table'
					))->values(),
				],
			],
			'face' => [
				'shape' => (new Schema\CachedEnum(
					new Schema\TableEnum('face_shapes', $this->connection),
					$this->redis,
					'face_shapes',
					'table'
				))->values(),
			],
			'general' => [
				'ethnic_group' => (new Schema\CachedEnum(
					new Schema\TableEnum('ethnic_groups', $this->connection),
					$this->redis,
					'ethnic_groups',
					'table'
				))->values(),
				'sex' => (new Schema\CachedEnum(
					new Schema\PostgresConstant('sex', $this->connection),
					$this->redis,
					'sex',
					'constant'
				))->values(),
			],
		];
	}
}
