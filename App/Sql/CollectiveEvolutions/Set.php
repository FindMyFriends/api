<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\CollectiveEvolutions;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Set implements Sql\Set {
	private $set;

	public function __construct(Sql\Clause $clause, array $additionalParameters = []) {
		$this->set = new Description\Set(
			$clause,
			$additionalParameters + ['evolved_at' => ':evolved_at']
		);
	}

	public function where(string $comparison, array $parameters = []): Sql\Where {
		return $this->set->where($comparison, $parameters);
	}

	public function sql(): string {
		return $this->set->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->set->parameters();
	}
}