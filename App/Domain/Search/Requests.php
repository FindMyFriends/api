<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

interface Requests {
	/**
	 * Move request to the new status
	 * @param int $watching
	 * @param string $status
	 * @param int|null $self
	 * @return int
	 */
	public function refresh(int $watching, string $status, ?int $self = null): int;

	/**
	 * All proceeded requests
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function all(Dataset\Selection $selection): \Iterator;
}