<?php
declare(strict_types = 1);

namespace FindMyFriends\Task;

use PhpAmqpLib;

abstract class Publisher {
	private const EXCHANGE = 'fmf.direct';

	/** @var \PhpAmqpLib\Connection\AbstractConnection */
	private $rabbitMq;

	final public function __construct(PhpAmqpLib\Connection\AbstractConnection $rabbitMq) {
		$this->rabbitMq = $rabbitMq;
	}

	final protected function add(array $body): void {
		$channel = $this->rabbitMq->channel();
		$channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
		$channel->basic_publish($this->message($body), self::EXCHANGE, $this->key());
	}

	private function message(array $body): PhpAmqpLib\Message\AMQPMessage {
		return new PhpAmqpLib\Message\AMQPMessage(
			json_encode($body),
			[
				'content_type' => 'application/json',
				'delivery_mode' => PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
			]
		);
	}

	abstract protected function key(): string;
}
