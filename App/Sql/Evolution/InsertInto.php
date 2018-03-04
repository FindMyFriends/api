<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Evolution;

use FindMyFriends\Sql\Description;
use Klapuch\Sql;

final class InsertInto implements Sql\InsertInto {
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

	public function returning(array $columns): Sql\Returning {
		return $this->insert->returning($columns);
	}

	public function onConflict(array $target = []): Sql\Conflict {
		return $this->insert->onConflict($target);
	}


	public function sql(): string {
		return $this->insert->sql();
	}
}