<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\CollectiveEvolutions;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Set implements Sql\Set {
	/** @var \FindMyFriends\Sql\Description\Set */
	private $set;

	public function __construct(Sql\Statement $statement, array $parameters) {
		$this->set = new Description\Set(
			$statement,
			[
				'evolved_at' => ':evolved_at',
				'general_age' => ':general_age',
			],
			(new Sql\FlatParameters(
				new Sql\UniqueParameters($parameters)
			))->binds()
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
