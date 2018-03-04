<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Hashids\Hashids;

trait Page {
	use TemplateDatabase {
		TemplateDatabase::setUp as databaseSetUp;
	}
	use Elasticsearch {
		Elasticsearch::setUp as elasticsearchSetUp;
	}

	private $configuration;

	protected function setUp(): void {
		parent::setUp();
		$this->configuration = [
			'HASHIDS' => [
				'demand' => ['hashid' => new Hashids()],
				'soulmate' => ['hashid' => new Hashids()],
				'evolution' => ['hashid' => new Hashids()],
			],
		];
		$this->databaseSetUp();
		$this->elasticsearchSetUp();
	}
}