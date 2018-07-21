<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use Klapuch\Storage;

final class RefreshMaterializedView implements Scheduling\Job {
	private const VIEWS = [
		'prioritized_evolution_fields',
	];

	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function fulfill(): void {
		foreach (self::VIEWS as $view) {
			(new Storage\NativeQuery(
				$this->database,
				sprintf('REFRESH MATERIALIZED VIEW CONCURRENTLY %s', $view)
			))->execute();
		}
	}

	public function name(): string {
		return 'RefreshMaterializedView';
	}
}
