<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Task;

/**
 * AMQP publisher
 */
final class AmqpPublisher extends Task\Publisher implements Publisher {
	protected function key(): string {
		return 'soulmate_demands';
	}

	public function publish(int $demand): void {
		$this->add(['id' => $demand]);
	}
}
