<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use FindMyFriends;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Demands belonging to everyone
 */
final class CollectiveDemands implements Demands {
	private $origin;
	private $database;

	public function __construct(Demands $origin, Storage\MetaPDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function ask(array $description): int {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new FindMyFriends\Sql\Demand\Select())->from(['collective_demands']),
				$selection
			)
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryPDO($this->database, $demand)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new Sql\AnsiSelect(['COUNT(*)']))->from(['demands']),
				$selection
			)
		))->field();
	}
}