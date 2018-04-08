<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Demand suited as AMQP message
 */
final class AmqpDemand implements Demand {
	private $origin;

	public function __construct(Demand $origin) {
		$this->origin = $origin;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted(null, function (array $demand): array {
				return [
					'id' => $demand['id'],
					'seeker_id' => $demand['seeker_id'],
					'request_id' => $demand['request_id'],
				];
			});
	}

	public function retract(): void {
		$this->origin->retract();
	}

	public function reconsider(array $description): void {
		$this->origin->reconsider($description);
	}
}
