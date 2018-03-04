<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Persisted soulmate
 */
final class StoredSoulmate implements Soulmate {
	private $id;
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		$soulmate = (new Storage\TypedQuery(
			$this->database,
			(new Storage\Clauses\AnsiSelect(
				[
					'id',
					'evolution_id',
					'demand_id',
					'position',
					'seeker_id',
					'new',
				]
			))->from(['suited_soulmates'])
				->where('id = ?')
				->sql(),
			[$this->id]
		))->row();
		return new Output\FilledFormat($format, $soulmate);
	}
}