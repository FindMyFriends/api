<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Evolution;

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
		return (new Schema\Description\ExplainedTableEnums(
			$this->database,
			$this->redis
		))->values() + (new Schema\Location\ExplainedTableEnums(
			$this->database,
			$this->redis
		))->values();
	}
}