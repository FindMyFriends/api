<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use FindMyFriends;
use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Demands belonging to the seeker
 */
final class IndividualDemands implements Demands {
	private $seeker;
	private $database;

	public function __construct(Access\User $seeker, Storage\MetaPDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new FindMyFriends\Sql\IndividualDemands\Select())
					->from(['collective_demands'])
					->where('seeker_id = :seeker_id', ['seeker_id' => $this->seeker->id()]),
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

	public function ask(array $description): int {
		return (new Storage\FlatQuery(
			$this->database,
			(new FindMyFriends\Sql\IndividualDemands\InsertInto('collective_demands'))->returning(['id'])->sql(),
			['seeker' => $this->seeker->id()] + $description
		))->field();
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new Sql\AnsiSelect(['COUNT(*)']))
					->from(['demands'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->field();
	}
}