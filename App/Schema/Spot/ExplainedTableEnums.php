<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Spot;

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
			'spot' => [
				'met_at' => [
					'timeline_side' => (new Schema\CachedEnum(
						new Schema\PostgresConstant('timeline_sides', $this->connection),
						$this->redis,
						'timeline_sides',
						'constant'
					))->values(),
				],
			],
		];
	}
}
