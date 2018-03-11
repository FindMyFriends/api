<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Elasticsearch;
use Klapuch\Access;
use Klapuch\Log;
use Klapuch\Storage;
use PhpAmqpLib;

final class Consumer {
	private $connection;
	private $elasticsearch;
	private $database;
	private $logs;
	private const EXCHANGE = 'fmf.direct',
		ROUTING_KEY = 'soulmate_demands',
		QUEUE = 'soulmate_searches';

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $connection,
		Elasticsearch\Client $elasticsearch,
		Storage\MetaPDO $database,
		Log\Logs $logs
	) {
		$this->connection = $connection;
		$this->elasticsearch = $elasticsearch;
		$this->database = $database;
		$this->logs = $logs;
	}

	public function consume(): void {
		$channel = $this->connection->channel();
		$channel->queue_declare(self::QUEUE, false, false, false, false);
		$channel->queue_bind(self::QUEUE, self::EXCHANGE, self::ROUTING_KEY);
		$channel->basic_consume(self::QUEUE, '', false, false, false, false, [$this, 'action']);
		while (count($channel->callbacks)) {
			$channel->wait();
		}
	}

	/**
	 * @internal
	 */
	public function action(PhpAmqpLib\Message\AMQPMessage $message): void {
		/** @var \PhpAmqpLib\Channel\AMQPChannel $channel */
		$channel = $message->delivery_info['channel'];
		try {
			$demand = json_decode($message->getBody(), true);
			(new SuitedSoulmates(
				new Access\FakeUser($demand['seeker_id']),
				$this->elasticsearch,
				$this->database
			))->find($demand['id']);
			$channel->basic_ack($message->delivery_info['delivery_tag']);
		} catch (\Throwable $ex) {
			$channel->basic_reject($message->delivery_info['delivery_tag'], true);
			$this->logs->put(
				new Log\PrettyLog(
					$ex,
					new Log\PrettySeverity(
						new Log\JustifiedSeverity(Log\Severity::ERROR)
					)
				)
			);
		}
	}
}