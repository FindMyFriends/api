<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Elasticsearch;
use FindMyFriends\Task;
use Klapuch\Log;
use Klapuch\Storage;
use PhpAmqpLib;

final class Consumer extends Task\Consumer {
	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Log\Logs $logs,
		Storage\Connection $connection,
		Elasticsearch\Client $elasticsearch
	) {
		parent::__construct($rabbitMq, $logs);
		$this->elasticsearch = $elasticsearch;
		$this->connection = $connection;
	}

	/** @internal */
	public function action(array $body): void {
		(new RequestedSoulmates(
			(new SubsequentRequests(
				$body['id'],
				$this->connection
			))->refresh('pending'),
			new SubsequentRequests($body['id'], $this->connection),
			new DemandedSoulmates($body['id'], $this->elasticsearch, $this->connection)
		))->seek();
	}

	protected function queue(): string {
		return 'soulmate_requests';
	}

	protected function key(): string {
		return 'soulmate_demands';
	}
}
