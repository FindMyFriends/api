<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Dataset;
use Klapuch\Storage;

/**
 * Demands belonging to everyone
 */
final class CollectiveDemands implements Demands {
	private $origin;
	private $database;

	public function __construct(Demands $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function ask(array $description): Demand {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\TypedQuery(
			$this->database,
			$selection->expression('SELECT * FROM collective_demands'),
			$selection->criteria([])
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO($this->database, $demand)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\NativeQuery(
			$this->database,
			$selection->expression('SELECT COUNT(*) FROM demands'),
			$selection->criteria([])
		))->field();
	}
}