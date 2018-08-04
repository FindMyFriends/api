<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\CollectiveEvolutionSpots;

use Klapuch\Sql;

final class Select implements Sql\Select {
	/** @var \Klapuch\Sql\AnsiSelect */
	private $select;

	public function __construct() {
		$this->select = new Sql\AnsiSelect(
			[
				'id',
				'evolution_id',
				'coordinates',
				'met_at',
				'assigned_at',
			]
		);
	}

	public function from(array $tables, array $parameters = []): Sql\From {
		return $this->select->from($tables, $parameters);
	}

	public function sql(): string {
		return $this->select->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->select->parameters();
	}
}
