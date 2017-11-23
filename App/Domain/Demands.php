<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;

interface Demands {
	/**
	 * Add a new demand within specified person
	 * @param mixed[] $description
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Demand
	 */
	public function ask(array $description): Demand;

	/**
	 * Go through all the demands
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \Iterator
	 */
	public function all(Dataset\Selection $selection): \Iterator;

	/**
	 * Count all demands
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int;
}