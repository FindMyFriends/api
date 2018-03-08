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

	public function extend(array $progress): Change {
		$id = (new Storage\FlatQuery(
			$this->database,
			(new FindMyFriends\Sql\Evolution\InsertInto('collective_evolutions'))->returning(['id'])->sql(),
			['seeker' => $this->seeker->id()] + $progress
		))->field();
		return new StoredChange($id, $this->database);
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		$clause = new Dataset\SelectiveClause(
			(new FindMyFriends\Sql\Evolution\Select())
				->from(['collective_evolutions'])
				->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
			$selection
		);
		$evolutions = (new Storage\TypedQuery(
			$this->database,
			$clause->sql(),
			$clause->parameters()->binds()
		))->rows();
		foreach ($evolutions as $change) {
			yield new StoredChange(
				$change['id'],
				new Storage\MemoryPDO($this->database, $change)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		$clause = new Dataset\SelectiveClause(
			(new Sql\AnsiSelect(['COUNT(*)']))
				->from(['evolutions'])
				->where('seeker_id = :seeker', ['seeker' => $this->seeker->id()]),
			$selection
		);
		return (new Storage\NativeQuery(
			$this->database,
			$clause->sql(),
			$clause->parameters()->binds()
		))->field();
	}
}