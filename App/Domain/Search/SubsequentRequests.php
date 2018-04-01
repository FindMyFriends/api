<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Request bind to the most first request
 */
final class SubsequentRequests implements Requests {
	private $demand;
	private $database;

	public function __construct(int $demand, Storage\MetaPDO $database) {
		$this->demand = $demand;
		$this->database = $database;
	}

	public function refresh(string $status, ?int $self = null): int {
		return (new Storage\TypedQuery(
			$this->database,
			'INSERT INTO soulmate_requests (demand_id, status, self_id)
			VALUES (?, ?, ?)
			RETURNING COALESCE(self_id, id)',
			[$this->demand, $status, $self]
		))->field();
	}

	public function all(Dataset\Selection $selection): \Iterator {
	}
}