<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use Elasticsearch\Client;
use FindMyFriends\Scheduling;

final class ElasticsearchReindexJob implements Scheduling\Job {
	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	public function __construct(Client $elasticsearch) {
		$this->elasticsearch = $elasticsearch;
	}

	public function fulfill(): void {
		$this->elasticsearch->index(['index' => 'relationships', 'type' => 'evolutions', 'body' => []]);
	}

	public function name(): string {
		return 'ElasticsearchReindexJob';
	}
}
