<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\IndividualDemands;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class Set implements Sql\Set {
	private $set;
	private $parameters;
	private const CONDITIONS = [
		'general_age' => ['general_age_from', 'general_age_to'],
		'location_met_at' => ['location_met_at_moment', 'location_met_at_timeline_side', 'location_met_at_approximation'],
		'location_coordinates' => ['location_coordinates_latitude', 'location_coordinates_longitude'],
		'note' => ['note'],
	];
	private const COLUMNS = [
		'general_age' => 'int4range(:general_age_from, :general_age_to)',
		'location_met_at' => 'ROW(:location_met_at_moment, :location_met_at_timeline_side, :location_met_at_approximation)',
		'location_coordinates' => 'POINT(:location_coordinates_latitude, :location_coordinates_longitude)',
		'note' => ':note',
	];

	public function __construct(Sql\Clause $clause, array $parameters) {
		$this->parameters = $parameters;
		$this->set = new Description\Set(
			$clause,
			array_reduce(
				array_keys(self::CONDITIONS),
				function (array $values, string $column): array {
					if (array_keys_exist($this->parameters, ...self::CONDITIONS[$column])) {
						$values[$column] = self::COLUMNS[$column];
					}
					return $values;
				},
				[]
			),
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
		return $this->set->parameters()->bind($this->parameters);
	}
}
