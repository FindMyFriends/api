<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Klapuch\Configuration;
use Predis;
use Tester;

trait RedisSession {
	/** @var \Predis\Client */
	protected $redis;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('redis-session', __DIR__ . '/../temp');
		$credentials = (new Configuration\ValidIni(
			new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
		))->read();
		$this->redis = new Predis\Client($credentials['REDIS-SESSION']['uri']);
		$this->redis->flushall();
	}
}
