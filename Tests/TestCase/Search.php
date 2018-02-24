<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

trait Search {
	use TemplateDatabase {
		TemplateDatabase::setUp as databaseSetUp;
	}
	use Elasticsearch {
		Elasticsearch::setUp as elasticsearchSetUp;
	}

	protected function setUp(): void {
		parent::setUp();
		$this->databaseSetUp();
		$this->elasticsearchSetUp();
	}
}