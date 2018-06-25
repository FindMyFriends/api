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

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Log\Logs $logs,
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch
	) {
		parent::__construct($rabbitMq, $logs);
		$this->elasticsearch = $elasticsearch;
		$this->database = $database;
	}

	/** @internal */
	public function action(array $body): void {
		(new RequestedSoulmates(
			(new SubsequentRequests(
				$body['id'],
				$this->database
			))->refresh('pending'),
			new SubsequentRequests($body['id'], $this->database),
			new SuitedSoulmates($body['id'], $this->elasticsearch, $this->database)
		))->seek();
	}

	protected function queue(): string {
		return 'soulmate_requests';
	}

	protected function key(): string {
		return 'soulmate_demands';
	}
}
