<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Predis;

final class FakeRedis implements Predis\ClientInterface {
	public function getProfile(): void {
	}

	public function getOptions(): void {
	}

	public function connect(): void {
	}

	public function disconnect(): void {
	}

	public function getConnection(): void {
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 */
	public function createCommand($method, $arguments = []): void {
	}

	public function executeCommand(Predis\Command\CommandInterface $command): void {
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments): void {
	}
}
