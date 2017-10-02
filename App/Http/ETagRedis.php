<?php
declare(strict_types = 1);
namespace FindMyFriends\Http;

use Predis;

final class ETagRedis implements Predis\ClientInterface {
	private $origin;

	public function __construct(Predis\ClientInterface $origin) {
		$this->origin = $origin;
	}

	public function getProfile(): Predis\Profile\ProfileInterface {
		return $this->origin->getProfile();
	}

	public function getOptions(): Predis\Configuration\OptionsInterface {
		return $this->origin->getOptions();
	}

	public function connect(): void {
		$this->origin->connect();
	}

	public function disconnect(): void {
		$this->origin->disconnect();
	}

	public function getConnection(): Predis\Connection\ConnectionInterface {
		return $this->origin->getConnection();
	}

	public function createCommand($method, $arguments = []): Predis\Command\CommandInterface {
		return $this->origin->createCommand($method, $arguments);
	}

	/**
	 * @return mixed
	 */
	public function executeCommand(Predis\Command\CommandInterface $command) {
		return $this->origin->executeCommand($command);
	}

	/**
	 * @return mixed
	 */
	public function __call($method, $arguments) {
		return $this->origin->__call($method, $arguments);
	}

	public function get($key): string {
		return $this->origin->get($this->key($key));
	}

	/**
	 * @return mixed
	 */
	public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null) {
		return $this->origin->set($this->key($key), $value);
	}

	public function exists($key): int {
		return $this->origin->exists($this->key($key));
	}

	private function key(string $key): string {
		return sprintf('_ETAG:%s', $key);
	}
}