<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Sql\SuitedSoulmates;
use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Persisted soulmate
 */
final class StoredSoulmate implements Soulmate {
	private $id;
	private $database;
	private $seeker;

	public function __construct(
		?int $id,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->id = $id;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	public function print(Output\Format $format): Output\Format {
		$soulmate = (new Storage\BuiltQuery(
			$this->database,
			(new SuitedSoulmates\Select())
				->from(['with_suited_soulmate_ownership(?)'], [$this->seeker->id()])
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
