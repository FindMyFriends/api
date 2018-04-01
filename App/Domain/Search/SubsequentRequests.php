<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Storage;

/**
 * Request bind to each other
 */
final class SubsequentRequests implements Requests {
	private $database;
	private $self;

	public function __construct(Storage\MetaPDO $database, ?int $self = null) {
		$this->database = $database;
		$this->self = $self;
	}

	public function refresh(int $id, string $status): int {
		return (new Storage\TypedQuery(
			$this->database,
			'INSERT INTO soulmate_requests (demand_id, status, self_id)
			VALUES (?, ?, ?)
			RETURNING COALESCE(self_id, id)',
			[$id, $status, $this->self]
		))->field();
	}
}