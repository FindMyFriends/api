<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Dataset;

/**
 * Demands able to be queued and later processed
 */
final class QueuedDemands implements Demands {
	/** @var \FindMyFriends\Domain\Demands */
	private $origin;

	/** @var \FindMyFriends\Domain\Search\Publisher */
	private $publisher;

	public function __construct(Demands $origin, Search\Publisher $publisher) {
		$this->origin = $origin;
		$this->publisher = $publisher;
	}

	public function all(Dataset\Selection $selection): \Iterator {
		return $this->origin->all($selection);
	}

	/**
	 * @param array $description
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function ask(array $description): int {
		$id = $this->origin->ask($description);
		$this->publisher->publish($id);
		return $id;
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}
