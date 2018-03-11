<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Demands able to be queued and later processed
 */
final class QueuedDemands implements Demands {
	private $origin;
	private $publisher;
	private $database;

	public function __construct(Demands $origin, Search\Publisher $publisher, Storage\MetaPDO $database) {
		$this->origin = $origin;
		$this->publisher = $publisher;
		$this->database = $database;
	}

	public function all(Dataset\Selection $selection): \Iterator {
		return $this->origin->all($selection);
	}

	public function ask(array $description): int {
		$id = $this->origin->ask($description);
		$this->publisher->publish(new StoredDemand($id, $this->database));
		return $id;
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}