<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

interface Requests {
	/**
	 * Move request to the new status
	 * @param string $status
	 * @param int|null $self
	 * @return int
	 */
	public function refresh(string $status, ?int $self = null): int;

	/**
	 * All proceeded requests
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function all(Dataset\Selection $selection): \Iterator;

	/**
	 * Counted all found requests
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int;
}
