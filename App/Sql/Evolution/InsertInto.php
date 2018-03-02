<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Evolution;

use FindMyFriends\Sql\Description;
use Klapuch\Storage\Clauses;
use Klapuch\Storage\Clauses\Conflict;
use Klapuch\Storage\Clauses\Returning;

final class InsertInto implements Clauses\InsertInto {
	private $insert;

	public function __construct(string $table, array $additionalParameters = []) {
		$this->insert = new Description\InsertInto(
			$table,
			$additionalParameters + [
				'evolved_at' => ':evolved_at',
				'seeker_id' => ':seeker',
			]
		);
	}

	public function returning(array $columns): Returning {
		return $this->insert->returning($columns);
	}

	public function onConflict(array $target = []): Conflict {
		return $this->insert->onConflict($target);
	}


	public function sql(): string {
		return $this->insert->sql();
	}
}