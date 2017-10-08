<?php
declare(strict_types = 1);
namespace FindMyFriends\TestCase;

use Klapuch\Ini;
use Predis;
use Tester;

trait Redis {
	/** @var \Predis\Client */
	protected $redis;

	/** @var mixed[] */
	protected $credentials;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('redis', __DIR__ . '/../Temporary');
		$this->credentials = (new Ini\ValidSource(
			new \SplFileInfo(__DIR__ . '/../Configuration/.config.local.ini')
		))->read();
		$this->redis = new Predis\Client($this->credentials['REDIS']['uri']);
		$this->redis->flushall();
	}
}