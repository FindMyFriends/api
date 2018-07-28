<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Location;

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
			'location' => [
				'met_at' => [
					'timeline_side' => (new Schema\CachedEnum(
						new Schema\PostgresEnum('timeline_sides', $this->database),
						$this->redis,
						'timeline_sides',
						'enum'
					))->values(),
				],
			],
		];
	}
}
