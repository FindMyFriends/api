<?php
declare(strict_types = 1);
namespace FindMyFriends\TestCase;

use FindMyFriends\Misc;
use Klapuch\Ini;
use Predis;

trait TemplateDatabase {
	/** @var \PDO */
	protected $database;

	/** @var mixed[] */
	protected $credentials;

	/** @var \FindMyFriends\Misc\Databases */
	private $databases;

	protected function setUp(): void {
		parent::setUp();
		$this->credentials = (new Ini\ValidSource(
			new \SplFileInfo(__DIR__ . '/../Configuration/.config.local.ini')
		))->read();
		$redis = new Predis\Client($this->credentials['REDIS']['uri']);
		$this->databases = new Misc\RandomDatabases($this->credentials['DATABASE'], $redis);
		$this->database = $this->databases->create();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->database = null;
		$this->databases->drop();
	}
}