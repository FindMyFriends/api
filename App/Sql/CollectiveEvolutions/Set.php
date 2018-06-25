<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\CollectiveEvolutions;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Set implements Sql\Set {
	/** @var \FindMyFriends\Sql\Description\Set */
	private $set;

	public function __construct(Sql\Clause $clause, array $parameters) {
		$this->set = new Description\Set(
			$clause,
			[
				'evolved_at' => ':evolved_at',
				'general_age' => "int4range(:general_age_from, :general_age_to, '[)')",
			],
			$parameters
		);
	}

	public function where(string $comparison, array $parameters = []): Sql\Where {
		return $this->set->where($comparison, $this->parameters()->bind($parameters)->binds());
	}

	public function sql(): string {
		return $this->set->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->set->parameters();
	}
}
