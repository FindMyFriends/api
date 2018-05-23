<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use FindMyFriends\Task;

/**
 * AMQP publisher
 */
final class AmqpPublisher extends Task\Publisher implements Publisher {
	protected function key(): string {
		return 'verification_message';
	}

	public function publish(string $email): void {
		$this->add(['email' => $email]);
	}
}
