<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Domain;
use Klapuch\Output;
use PhpAmqpLib;

final class Publisher {
	private $connection;
	private const EXCHANGE = 'fmf.direct',
		ROUTING_KEY = 'soulmate_demands';

	public function __construct(PhpAmqpLib\Connection\AbstractConnection $connection) {
		$this->connection = $connection;
	}

	public function publish(Domain\Demand $demand): void {
		$channel = $this->connection->channel();
		$channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
		$message = new PhpAmqpLib\Message\AMQPMessage(
			(new Domain\AmqpDemand($demand))->print(new Output\Json())->serialization(),
			[
				'content_type' => 'application/json',
				'delivery_mode' => PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
			]
		);
		$channel->basic_publish($message, self::EXCHANGE, self::ROUTING_KEY);
	}
}