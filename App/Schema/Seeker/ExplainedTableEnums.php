<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker;

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
			'general' => [
				'ethnic_group' => (new Schema\CachedEnum(
					new Schema\TableEnum('ethnic_groups', $this->database),
					$this->redis,
					'ethnic_groups',
					'table'
				))->values(),
				'sex' => (new Schema\CachedEnum(
					new Schema\PostgresEnum('sex_enum', $this->database),
					$this->redis,
					'sex',
					'enum'
				))->values(),
			],
		];
	}
}
