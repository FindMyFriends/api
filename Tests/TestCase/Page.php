<?php
declare(strict_types = 1);
namespace FindMyFriends\TestCase;

trait Page {
	use Redis {
		Redis::setUp as redisSetUp;
	} use TemplateDatabase {
		TemplateDatabase::setUp as databaseSetUp;
	}

	protected function setUp(): void {
		parent::setUp();
		$this->databaseSetUp();
		$this->redisSetUp();
	}
}