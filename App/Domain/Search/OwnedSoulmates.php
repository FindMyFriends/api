<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends;
use FindMyFriends\Domain\Access;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Soulmates suited for the particular seeker
 */
final class OwnedSoulmates implements Soulmates {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Access\Seeker $seeker, Storage\Connection $connection) {
		$this->seeker = $seeker;
		$this->connection = $connection;
	}

	public function matches(Dataset\Selection $selection): \Iterator {
		$matches = (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new FindMyFriends\Sql\SuitedSoulmates\Select())
					->from(['suited_soulmates'])
					->where('is_soulmate_owned(id, :seeker)', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->rows();
		foreach ($matches as $match) {
			yield new StoredSoulmate(
				$match['id'],
				new Storage\MemoryConnection($this->connection, $match)
			);
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new Sql\AnsiSelect(['count(*)']))
					->from(['suited_soulmates'])
					->where('is_soulmate_owned(id, :seeker)', ['seeker' => $this->seeker->id()]),
				$selection
			)
		))->field();
	}
}
