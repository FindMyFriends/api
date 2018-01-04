<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Klapuch\Dataset;

interface Chain {
	/**
	 * Extend chain with a new change
	 * @param mixed[] $progress
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Evolution\Change
	 */
	public function extend(array $progress): Change;

	/**
	 * The whole history in evolution chain
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \Iterator
	 */
	public function changes(Dataset\Selection $selection): \Iterator;

	/**
	 * Count all changes in evolution chain
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int;
}