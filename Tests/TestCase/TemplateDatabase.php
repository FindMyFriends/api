<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use FindMyFriends\Misc;
use Klapuch\Configuration;

trait TemplateDatabase {
	/** @var \Klapuch\Storage\MetaPDO */
	protected $database;

	/** @var \FindMyFriends\Misc\Databases */
	private $databases;

	protected function setUp(): void {
		parent::setUp();
		$credentials = (new Configuration\ValidIni(
			new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
		))->read();
		$this->databases = new Misc\RandomDatabases($credentials['DATABASE']);
		$this->database = $this->databases->create();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->database = null;
		$this->databases->drop();
	}
}