<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Evolution;

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
		return (new Schema\Description\ExplainedTableEnums(
			$this->connection,
			$this->redis
		))->values() + (new Schema\Spot\ExplainedTableEnums(
			$this->connection,
			$this->redis
		))->values();
	}
}
