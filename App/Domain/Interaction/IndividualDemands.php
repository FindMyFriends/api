<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends;
use FindMyFriends\Domain\Access;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Demands belonging to the seeker
 */
final class IndividualDemands implements Demands {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Access\Seeker $seeker, Storage\Connection $connection) {
		$this->seeker = $seeker;
		$this->connection = $connection;
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$demands = (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new FindMyFriends\Sql\IndividualDemands\Select())
					->from(['collective_demands'])
					->where('seeker_id = :seeker_id', ['seeker_id' => $this->seeker->id()]),
				$selection
			)
		))->rows();
		foreach ($demands as $demand) {
			yield new StoredDemand(
				$demand['id'],
				new Storage\MemoryConnection($this->connection, $demand)
			);
		}
	}

	public function ask(array $description): int {
		return (new Storage\TypedQuery(
			$this->connection,
			(new FindMyFriends\Sql\IndividualDemands\InsertInto('collective_demands'))->returning(['id'])->sql(),
			(new Sql\FlatParameters(
				new Sql\UniqueParameters(['seeker' => $this->seeker->id()] + $description)
			))->binds()
		))->field();
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new Sql\AnsiSelect(['COUNT(*)']))
					->from(['demands'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->field();
	}
}
