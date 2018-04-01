<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Domain;
use Klapuch\Output\Format;
use Klapuch\Output\Json;
use Klapuch\Storage;
use PhpAmqpLib;

final class Publisher {
	private $connection;
	private $requests;
	private $database;
	private const EXCHANGE = 'fmf.direct',
		ROUTING_KEY = 'soulmate_demands';

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $connection,
		Requests $requests,
		Storage\MetaPDO $database
	) {
		$this->connection = $connection;
		$this->requests = $requests;
		$this->database = $database;
	}

	public function publish(int $demand): void {
		$channel = $this->connection->channel();
		$channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
		$channel->basic_publish(
			$this->message(
				(new Domain\AmqpDemand(
					new Domain\StoredDemand($demand, $this->database)
				))->print(new Json(['request_id' => $this->requests->refresh($demand, 'pending')]))
			),
			self::EXCHANGE,
			self::ROUTING_KEY
		);
	}

	private function message(Format $format): PhpAmqpLib\Message\AMQPMessage {
		return new PhpAmqpLib\Message\AMQPMessage(
			$format->serialization(),
			[
				'content_type' => 'application/json',
				'delivery_mode' => PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
			]
		);
	}
}