<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use Klapuch\Storage;

final class RefreshMaterializedViewJob implements Scheduling\Job {
	/** @var string */
	private $view;

	/** @var \PDO */
	private $database;

	public function __construct(string $view, \PDO $database) {
		$this->view = $view;
		$this->database = $database;
	}

	public function fulfill(): void {
		(new Storage\NativeQuery(
			$this->database,
			sprintf('REFRESH MATERIALIZED VIEW CONCURRENTLY %s', $this->view)
		))->execute();
	}

	public function name(): string {
		return 'RefreshMaterializedViewJob';
	}
}
