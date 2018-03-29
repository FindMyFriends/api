<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\SuitedSoulmates;

use Klapuch\Sql;

final class Select implements Sql\Select {
	private $select;

	public function __construct() {
		$this->select = new Sql\AnsiSelect(
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
		);
	}

	public function from(array $tables): Sql\From {
		return $this->select->from($tables);
	}

	public function sql(): string {
		return $this->select->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->select->parameters();
	}
}