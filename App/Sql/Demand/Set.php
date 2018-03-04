<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Demand;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Set implements Sql\Set {
	private $set;

	public function __construct(Sql\Clause $clause, array $additionalParameters = []) {
		$this->set = new Description\Set(
			$clause,
			$additionalParameters + [
				'general_age' => 'int4range(:general_age_from, :general_age_to)',
				'location_met_at' => 'ROW(:location_met_at_moment, :location_met_at_timeline_side, :location_met_at_approximation)',
				'location_coordinates' => 'POINT(:location_coordinates_latitude, :location_coordinates_longitude)',
			]
		);
	}

	public function where(string $comparison): Sql\Where {
		return $this->set->where($comparison);
	}

	public function sql(): string {
		return $this->set->sql();
	}
}