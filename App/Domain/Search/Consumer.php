<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Elasticsearch;
use FindMyFriends\Task;
use Klapuch\Log;
use Klapuch\Storage;
use PhpAmqpLib;

final class Consumer extends Task\Consumer {
	private $elasticsearch;
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
	public function action(PhpAmqpLib\Message\AMQPMessage $message): void {
		$demand = (int) $message->getBody();
		(new RequestedSoulmates(
			(new SubsequentRequests(
				$demand,
				$this->database
			))->refresh('pending'),
			new SubsequentRequests($demand, $this->database),
			new SuitedSoulmates($demand, $this->elasticsearch, $this->database)
		))->seek();
	}

	protected function queue(): string {
		return 'soulmate_requests';
	}

	protected function key(): string {
		return 'soulmate_demands';
	}
}
