<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Klapuch\Configuration;
use Predis;
use Tester;

trait Redis {
	/** @var \Predis\Client */
	protected $redis;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('redis', __DIR__ . '/../temp');
		$credentials = (new Configuration\ValidIni(
			new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
		))->read();
		$this->redis = new Predis\Client($credentials['REDIS']['uri']);
		$this->redis->flushall();
	}
}