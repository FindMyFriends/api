<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Spot;

use Klapuch\Sql;

final class Set implements Sql\Set {
	private const CONDITIONS = [
		'coordinates' => ['coordinates_latitude', 'coordinates_longitude'],
		'met_at' => ['met_at_moment', 'met_at_timeline_side', 'met_at_approximation'],
	];
	private const SET = [
		'coordinates' => 'POINT(:coordinates_latitude, :coordinates_longitude)',
		'met_at' => 'ROW(:met_at_moment, :met_at_timeline_side, :met_at_approximation)',
	];

	/** @var \Klapuch\Sql\AnsiSet */
	private $set;

	/** @var mixed[] */
	private $parameters;

	public function __construct(Sql\Statement $statement, array $parameters) {
		$this->parameters = (new Sql\FlatParameters(
			new Sql\UniqueParameters($parameters)
		))->binds();
		$this->set = new Sql\AnsiSet(
			$statement,
			array_reduce(
				array_keys(self::CONDITIONS),
				function (array $values, string $column): array {
					if (array_keys_exist($this->parameters, ...self::CONDITIONS[$column])) {
						$values[$column] = self::SET[$column];
					}
					return $values;
				},
				[]
			)
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
