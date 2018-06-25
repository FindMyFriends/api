<?php
declare(strict_types = 1);

namespace FindMyFriends\Task;

use Klapuch\Log;
use PhpAmqpLib;

abstract class Consumer {
	private const EXCHANGE = 'fmf.direct';

	/** @var \PhpAmqpLib\Connection\AbstractConnection */
	private $connection;

	/** @var \Klapuch\Log\Logs */
	private $logs;

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Log\Logs $logs
	) {
		$this->connection = $rabbitMq;
		$this->logs = $logs;
	}

	final public function consume(): void {
		$channel = $this->connection->channel();
		$channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
		$channel->queue_declare($this->queue(), false, true, false, false);
		$channel->queue_bind($this->queue(), self::EXCHANGE, $this->key());
		$channel->basic_consume($this->queue(), '', false, false, false, false, [$this, 'callback']);
		while (count($channel->callbacks)) {
			$channel->wait();
		}
	}

	/** @internal */
	final public function callback(PhpAmqpLib\Message\AMQPMessage $message): void {
		/** @var \PhpAmqpLib\Channel\AMQPChannel $channel */
		$channel = $message->delivery_info['channel'];
		try {
			$this->action(json_decode($message->getBody(), true));
			$channel->basic_ack($message->delivery_info['delivery_tag']);
		} catch (\Throwable $ex) {
			$channel->basic_reject($message->delivery_info['delivery_tag'], true);
			$this->logs->put($ex, new Log\CurrentEnvironment());
		}
	}

	abstract protected function action(array $body): void;
	abstract protected function queue(): string;
	abstract protected function key(): string;
}
