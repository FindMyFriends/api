<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Elasticsearch;
use FindMyFriends\Domain\Access;
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
		$demand = json_decode($message->getBody(), true);
		(new RequestedSoulmates(
			$demand['request_id'],
			new SubsequentRequests($demand['id'], $this->database),
			new SuitedSoulmates(
				$demand['id'],
				new Access\FakeSeeker((string) $demand['seeker_id']),
				$this->elasticsearch,
				$this->database
			)
		))->seek();
	}

	protected function queue(): string {
		return 'soulmate_requests';
	}

	protected function key(): string {
		return 'soulmate_demands';
	}
}
