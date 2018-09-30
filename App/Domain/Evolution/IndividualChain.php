<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends;
use FindMyFriends\Domain\Access;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Chain for one particular seeker
 */
final class IndividualChain implements Chain {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Access\Seeker $seeker, Storage\Connection $connection) {
		$this->seeker = $seeker;
		$this->connection = $connection;
	}

	public function extend(array $progress): int {
		return (new Storage\TypedQuery(
			$this->connection,
			(new FindMyFriends\Sql\CollectiveEvolutions\InsertInto('collective_evolutions'))->returning(['id'])->sql(),
			(new Sql\FlatParameters(
				new Sql\UniqueParameters(['seeker' => $this->seeker->id()] + $progress)
			))->binds()
		))->field();
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		$evolutions = (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new FindMyFriends\Sql\CollectiveEvolutions\Select())
					->from(['collective_evolutions'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->rows();
		foreach ($evolutions as $change) {
			yield new StoredChange(
				$change['id'],
				new Storage\MemoryConnection($this->connection, $change)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new Sql\AnsiSelect(['COUNT(*)']))
					->from(['evolutions'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->field();
	}
}
