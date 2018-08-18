<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use Elasticsearch;
use FindMyFriends\Scheduling;

final class ElasticsearchReindex implements Scheduling\Job {
	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	public function __construct(Elasticsearch\Client $elasticsearch) {
		$this->elasticsearch = $elasticsearch;
	}

	public function fulfill(): void {
		$this->elasticsearch->index(['index' => 'relationships', 'type' => 'evolutions', 'body' => []]);
	}

	public function name(): string {
		return 'ElasticsearchReindex';
	}
}
