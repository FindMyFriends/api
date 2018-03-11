<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends;
use FindMyFriends\Domain;
use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Stored change
 */
final class StoredChange implements Change {
	private $id;
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function affect(array $changes): void {
		(new Storage\FlatQuery(
			$this->database,
			(new FindMyFriends\Sql\Evolution\Set(
				new Sql\AnsiUpdate('collective_evolutions')
			))->where('id = :id')->sql(),
			['id' => $this->id] + $changes
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$evolution = (new Storage\BuiltQuery(
			$this->database,
			(new FindMyFriends\Sql\Evolution\Select())
				->from(['collective_evolutions'])
				->where('id = ?', [$this->id])
		))->row();
		return (new Domain\CompleteDescription($format, $evolution))
			->with('id', $evolution['id'])
			->with('evolved_at', $evolution['evolved_at']);
	}

	public function revert(): void {
		(new Storage\ApplicationQuery(
			new Storage\NativeQuery(
				$this->database,
				'DELETE FROM evolutions WHERE id = ?',
				[$this->id]
			)
		))->execute();
	}
}