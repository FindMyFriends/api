<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\IndividualDemands;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class InsertInto implements Sql\InsertInto {
	/** @var \FindMyFriends\Sql\Description\InsertInto */
	private $insert;

	public function __construct(string $table) {
		$this->insert = new Description\InsertInto(
			$table,
			[
				'location_met_at' => 'ROW(:location_met_at_moment, :location_met_at_timeline_side, :location_met_at_approximation)',
				'seeker_id' => ':seeker',
				'general_age' => 'int4range(:general_age_from, :general_age_to)',
				'location_coordinates' => 'POINT(:location_coordinates_latitude, :location_coordinates_longitude)',
				'note' => ':note',
			]
		);
	}

	public function returning(array $columns, array $parameters = []): Sql\Returning {
		return $this->insert->returning($columns, $parameters);
	}

	public function onConflict(array $target = []): Sql\Conflict {
		return $this->insert->onConflict($target);
	}

	public function sql(): string {
		return $this->insert->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->insert->parameters();
	}
}
