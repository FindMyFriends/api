<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends;
use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Chain for one particular seeker
 */
final class IndividualChain implements Chain {
	private $seeker;
	private $database;

	public function __construct(Access\User $seeker, Storage\MetaPDO $database) {
		$this->seeker = $seeker;
		$this->database = $database;
	}

	public function extend(array $progress): int {
		return (new Storage\TypedQuery(
			$this->database,
			(new FindMyFriends\Sql\CollectiveEvolutions\InsertInto('collective_evolutions'))->returning(['id'])->sql(),
			(new Sql\FlatParameters(
				new Sql\UniqueParameters(['seeker' => $this->seeker->id()] + $progress)
			))->binds()
		))->field();
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		$evolutions = (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new FindMyFriends\Sql\CollectiveEvolutions\Select())
					->from(['collective_evolutions'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->rows();
		foreach ($evolutions as $change) {
			yield new StoredChange(
				$change['id'],
				new Storage\MemoryPDO($this->database, $change)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new Sql\AnsiSelect(['COUNT(*)']))
					->from(['evolutions'])
					->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->field();
	}
}
