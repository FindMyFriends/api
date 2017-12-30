<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;
use Predis;

final class RandomDatabases implements Databases {
	private $credentials;
	private $name;
	private $redis;

	public function __construct(array $credentials, Predis\ClientInterface $redis) {
		$this->credentials = $credentials;
		$this->name = 'test_' . bin2hex(random_bytes(20));
		$this->redis = $redis;
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
		(new Storage\SafePDO(
			sprintf($this->credentials['dsn'], 'postgres'),
			$this->credentials['user'],
			$this->credentials['password']
		))->exec(sprintf('DROP DATABASE %s', $this->name));
	}

	private function database(string $name): Storage\MetaPDO {
		return new Storage\MetaPDO(
			new Storage\SafePDO(
				sprintf($this->credentials['dsn'], $name),
				$this->credentials['user'],
				$this->credentials['password']
			),
			$this->redis
		);
	}
}