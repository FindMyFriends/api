<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Predis;

final class ETagRedis implements Predis\ClientInterface {
	private const SECTION = '_ETAG:';
	private $origin;

	public function __construct(Predis\ClientInterface $origin) {
		$this->origin = $origin;
	}

	public function getProfile() {
		return $this->origin->getProfile();
	}

	public function getOptions() {
		return $this->origin->getOptions();
	}

	public function connect(): void {
		$this->origin->connect();
	}

	public function disconnect(): void {
		$this->origin->disconnect();
	}

	public function getConnection() {
		return $this->origin->getConnection();
	}

	public function createCommand($method, $arguments = []) {
		return $this->origin->createCommand($method, $arguments);
	}

	public function executeCommand(Predis\Command\CommandInterface $command) {
		return $this->origin->executeCommand($command);
	}

	public function __call($method, $arguments) {
		return $this->origin->__call($method, $arguments);
	}

	public function get($key): string {
		return $this->origin->get(self::SECTION . $key);
	}

	public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null) {
		return $this->origin->set(self::SECTION . $key, $value);
	}

	public function exists($key): int {
		return $this->origin->exists(self::SECTION . $key);
	}
}