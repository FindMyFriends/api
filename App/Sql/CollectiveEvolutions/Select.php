<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\CollectiveEvolutions;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Select implements Sql\Select {
	/** @var \FindMyFriends\Sql\Description\Select */
	private $select;

	public function __construct() {
		$this->select = new Description\Select(['evolved_at', 'seeker_id']);
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
