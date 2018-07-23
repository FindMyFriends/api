<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Schema;
use FindMyFriends\Sql\Description;
use Klapuch\Storage;

final class PrioritizedColumns implements Schema\Columns {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(Storage\MetaPDO $database, Access\Seeker $seeker) {
		$this->database = $database;
		$this->seeker = $seeker;
	}

	public function values(): array {
		$columns = (new Storage\TypedQuery(
			$this->database,
			'SELECT columns
			FROM prioritized_evolution_fields
			WHERE seeker_id = ?',
			[$this->seeker->id()]
		))->field();
		return $columns === false ?
			[] :
			(new Description\Mapping())->application(json_decode($columns, true));
	}
}