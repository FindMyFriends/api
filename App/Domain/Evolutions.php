<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;

interface Evolutions {
	/**
	 * Add a new change to evolution chain
	 * @param mixed[] $progress
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Evolution
	 */
	public function evolve(array $progress): Evolution;

	/**
	 * All realized changes in evolution chain
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \FindMyFriends\Domain\Evolution[]
	 */
	public function changes(Dataset\Selection $selection): \Traversable;

	/**
	 * Count all changes in evolution chain
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int;
}