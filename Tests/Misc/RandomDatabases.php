<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;
use Predis;

final class RandomDatabases implements Databases {
	private $credentials;
	private $name;

	public function __construct(array $credentials) {
		$this->credentials = $credentials;
		$this->name = 'test_' . bin2hex(random_bytes(20));
	}

	public function create(): \PDO {
		$this->database('postgres')->exec(
			sprintf(
				'CREATE DATABASE %s WITH TEMPLATE %s',
				$this->name,
				$this->credentials['template']
			)
		);
		return $this->database($this->name);
	}

	public function drop(): void {
		$this->database('postgres')->exec(
			sprintf('DROP DATABASE %s', $this->name)
		);
	}

	private function database(string $name): Storage\MetaPDO {
		return new Storage\MetaPDO(
			new Storage\SafePDO(
				sprintf($this->credentials['dsn'], $name),
				$this->credentials['user'],
				$this->credentials['password']
			),
			new class implements Predis\ClientInterface {
				private $cache;

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

				public function createCommand($method, $arguments = []): void {
				}

				public function executeCommand(Predis\Command\CommandInterface $command): void {
				}

				/**
				 * @param string $method
				 * @param array $arguments
				 * @return bool|mixed|null
				 */
				public function __call($method, $arguments) {
					if (in_array($method, ['exists', 'hexists'], true))
						return false;
					elseif ($method === 'hset')
						$this->cache['hget'][$arguments[0]][$arguments[1]] = $arguments[2];
					elseif ($method === 'hget')
						return $this->cache[$method][$arguments[0]][$arguments[1]];
					elseif ($method === 'set')
						$this->cache['get'][$arguments[0]] = $arguments[1];
					elseif ($method === 'get')
						return $this->cache[$method][$arguments[0]];
					return null;
				}
			}
		);
	}
}