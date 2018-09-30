<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use Klapuch\Storage;

final class RefreshMaterializedView implements Scheduling\Job {
	private const VIEWS = [
		'prioritized_evolution_fields',
	];

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function fulfill(): void {
		foreach (self::VIEWS as $view) {
			(new Storage\NativeQuery(
				$this->connection,
				sprintf('REFRESH MATERIALIZED VIEW CONCURRENTLY %s', $view)
			))->execute();
		}
	}

	public function name(): string {
		return 'RefreshMaterializedView';
	}
}
