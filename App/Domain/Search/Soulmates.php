<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

interface Soulmates {
	/**
	 * Try to find all matches
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function seek(): void;

	/**
	 * All found matches
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function matches(Dataset\Selection $selection): \Iterator;

	/**
	 * Counted all found matches
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int;
}