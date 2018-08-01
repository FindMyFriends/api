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
				'seeker_id' => ':seeker',
				'general_age' => 'int4range(:general_age_from, :general_age_to)',
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
