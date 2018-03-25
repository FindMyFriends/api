<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Persisted soulmate
 */
final class StoredSoulmate implements Soulmate {
	private $id;
	private $database;

	public function __construct(?int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$soulmate = (new Storage\BuiltQuery(
			$this->database,
			(new Sql\AnsiSelect(
				[
					'id',
					'evolution_id',
					'demand_id',
					'position',
					'seeker_id',
					'new',
					'related_at',
					'searched_at',
					'is_correct',
				]
			))->from(['suited_soulmates'])
				->where('id = ?', [$this->id])
		))->row();
		return new Output\FilledFormat($format, $soulmate);
	}

	public function clarify(array $clarification): int {
		return (new Storage\BuiltQuery(
			$this->database,
			(new Sql\PreparedUpdate(new Sql\AnsiUpdate('soulmates')))
				->set($clarification)
				->where('id = :id', ['id' => $this->id])
				->returning(['id'])
		))->field();
	}
}