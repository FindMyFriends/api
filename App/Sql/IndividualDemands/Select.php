<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\IndividualDemands;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Select implements Sql\Select {
	private $select;

	public function __construct(array $additionalColumns = []) {
		$this->select = new Description\Select(
			array_merge(
				[
					'seeker_id',
					'created_at',
					'location_coordinates',
					'location_met_at',
					'note',
				],
				$additionalColumns
			)
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
