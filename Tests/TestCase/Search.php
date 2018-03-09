<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

trait Search {
	use TemplateDatabase {
		TemplateDatabase::setUp as databaseSetUp;
		TemplateDatabase::tearDown as databaseTearDown;
	}
	use Elasticsearch {
		Elasticsearch::setUp as elasticsearchSetUp;
	}
	use Mockery {
		Mockery::tearDown as mockeryTearDown;
	}

	protected function setUp(): void {
		parent::setUp();
		$this->databaseSetUp();
		$this->elasticsearchSetUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->databaseTearDown();
		$this->mockeryTearDown();
	}
}