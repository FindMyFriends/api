<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Request bind to the most first request
 */
final class SubsequentRequests implements Requests {
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

	public function refresh(int $demand, string $status, ?int $self = null): int {
		return (new Storage\TypedQuery(
			$this->database,
			'INSERT INTO soulmate_requests (demand_id, status, self_id)
			VALUES (?, ?, ?)
			RETURNING COALESCE(self_id, id)',
			[$demand, $status, $self]
		))->field();
	}

	public function all(Dataset\Selection $selection): \Iterator {
	}
}