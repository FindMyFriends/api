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

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$soulmate = (new Storage\BuiltQuery(
			$this->database,
			(new SuitedSoulmates\Select())
				->from(['suited_soulmates'])
				->where('id = ?', [$this->id])
		))->row();
		return new Output\FilledFormat($format, $soulmate);
	}

	public function clarify(array $clarification): void {
		(new Storage\BuiltQuery(
			$this->database,
			(new Sql\PreparedUpdate(new Sql\AnsiUpdate('soulmates')))
				->set($clarification)
				->where('id = :id', ['id' => $this->id])
		))->execute();
	}
}
