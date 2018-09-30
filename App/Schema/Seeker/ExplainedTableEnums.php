<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker;

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
