<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

interface Requests {
	/**
	 * Move request to the new status
	 * @param int $id
	 * @param string $status
	 * @return int
	 */
	public function refresh(int $id, string $status): int;
}