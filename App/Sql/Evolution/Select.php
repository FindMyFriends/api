<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Evolution;

use FindMyFriends\Sql\Description;
use Klapuch\Storage\Clauses;

final class Select implements Clauses\Select {
	private $select;

	public function __construct(array $additionalColumns = []) {
		$this->select = new Description\Select(
			array_merge(['evolved_at'], $additionalColumns)
		);
	}

	public function from(array $tables): Clauses\From {
		return $this->select->from($tables);
	}

	public function sql(): string {
		return $this->select->sql();
	}
}