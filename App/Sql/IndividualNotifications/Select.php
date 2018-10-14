<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\IndividualNotifications;

use Klapuch\Sql;

final class Select implements Sql\Select {
	/** @var \Klapuch\Sql\AnsiSelect */
	private $select;

	public function __construct() {
		$this->select = new Sql\AnsiSelect(
			[
				'id',
				'seeker_id',
				'involved_seeker_id',
				'type',
				'seen_at',
				'seen',
				'notified_at',
			]
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
