<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use PhpAmqpLib;

/**
 * AMQP publisher
 */
final class AmqpPublisher implements Publisher {
	private $connection;
	private const EXCHANGE = 'fmf.direct',
		ROUTING_KEY = 'soulmate_demands';

	public function __construct(PhpAmqpLib\Connection\AbstractConnection $connection) {
		$this->connection = $connection;
	}

	public function publish(int $demand): void {
		$channel = $this->connection->channel();
		$channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
		$channel->basic_publish($this->message($demand), self::EXCHANGE, self::ROUTING_KEY);
	}

	private function message(int $demand): PhpAmqpLib\Message\AMQPMessage {
		return new PhpAmqpLib\Message\AMQPMessage(
			json_encode(['id' => $demand]),
			[
				'content_type' => 'application/json',
				'delivery_mode' => PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
			]
		);
	}
}
