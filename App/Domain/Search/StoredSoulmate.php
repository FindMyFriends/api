<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Sql\SuitedSoulmates;
use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Persisted soulmate
 */
final class StoredSoulmate implements Soulmate {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(int $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	public function print(Output\Format $format): Output\Format {
		$soulmate = (new Storage\BuiltQuery(
			$this->connection,
			(new SuitedSoulmates\Select())
				->from(['suited_soulmates'])
				->where('id = ?', [$this->id])
		))->row();
		return new Output\FilledFormat($format, $soulmate);
	}

	public function clarify(bool $correct): void {
		(new Storage\BuiltQuery(
			$this->connection,
			(new Sql\PreparedUpdate(new Sql\AnsiUpdate('soulmates')))
				->set(['is_correct' => $correct])
				->where('id = :id', ['id' => $this->id])
		))->execute();
	}

	public function expose(): void {
		(new Storage\BuiltQuery(
			$this->connection,
			(new Sql\PreparedUpdate(new Sql\AnsiUpdate('soulmates')))
				->set(['is_exposed' => true])
				->where('id = :id', ['id' => $this->id])
		))->execute();
	}
}
