<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Schema;
use FindMyFriends\Sql\Description;
use Klapuch\Storage;

final class PrioritizedColumns implements Schema\Columns {
	private const DEFAULT_COLUMNS = [
		'general_sex' => 1,
		'general_firstname' => 2,
		'general_lastname' => 3,
	];

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(Storage\Connection $connection, Access\Seeker $seeker) {
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	public function values(): array {
		$columns = (new Storage\TypedQuery(
			$this->connection,
			'SELECT columns
			FROM prioritized_evolution_fields
			WHERE seeker_id = ?',
			[$this->seeker->id()]
		))->field();
		return (new Description\Mapping())->application(
			$columns === false
				? self::DEFAULT_COLUMNS
				: json_decode($columns, true)
		);
	}
}
