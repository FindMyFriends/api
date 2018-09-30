<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends;
use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Stored change
 */
final class StoredChange implements Change {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(int $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	public function affect(array $changes): void {
		(new Storage\BuiltQuery(
			$this->connection,
			(new FindMyFriends\Sql\CollectiveEvolutions\Set(
				new Sql\AnsiUpdate('collective_evolutions'),
				$changes
			))->where('id = :id', ['id' => $this->id])
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$evolution = (new Storage\BuiltQuery(
			$this->connection,
			(new FindMyFriends\Sql\CollectiveEvolutions\Select())
				->from(['collective_evolutions'])
				->where('id = ?', [$this->id])
		))->row();
		return (new CompleteDescription($format, $evolution))
			->with('id', $evolution['id'])
			->with('evolved_at', $evolution['evolved_at'])
			->with('seeker_id', $evolution['seeker_id']);
	}

	public function revert(): void {
		(new Storage\ApplicationQuery(
			new Storage\NativeQuery(
				$this->connection,
				'DELETE FROM evolutions WHERE id = ?',
				[$this->id]
			)
		))->execute();
	}
}
