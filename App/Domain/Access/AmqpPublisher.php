<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use PhpAmqpLib;

/**
 * AMQP publisher
 */
final class AmqpPublisher implements Publisher {
	private $connection;
	private const EXCHANGE = 'fmf.direct',
		ROUTING_KEY = 'verification_message';

	public function __construct(PhpAmqpLib\Connection\AbstractConnection $connection) {
		$this->connection = $connection;
	}

	public function publish(string $email): void {
		$channel = $this->connection->channel();
		$channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
		$channel->basic_publish($this->message($email), self::EXCHANGE, self::ROUTING_KEY);
	}

	private function message(string $email): PhpAmqpLib\Message\AMQPMessage {
		return new PhpAmqpLib\Message\AMQPMessage(
			json_encode(['email' => $email]),
			[
				'content_type' => 'application/json',
				'delivery_mode' => PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
			]
		);
	}
}
