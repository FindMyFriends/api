<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Evolution;

use FindMyFriends\Sql\Description;
use Klapuch\Storage\Clauses;
use Klapuch\Storage\Clauses\Where;

final class Set implements Clauses\Set {
	private $set;

	public function __construct(Clauses\Clause $clause, array $additionalParameters = []) {
		$this->set = new Description\Set(
			$clause,
			$additionalParameters + ['evolved_at' => ':evolved_at']
		);
	}

	public function where(string $comparison): Where {
		return $this->set->where($comparison);
	}

	public function sql(): string {
		return $this->set->sql();
	}
}