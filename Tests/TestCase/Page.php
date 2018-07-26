<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use FindMyFriends;
use Klapuch\Configuration;

trait Page {
	use TemplateDatabase {
		TemplateDatabase::setUp as databaseSetUp;
	}
	use Elasticsearch {
		Elasticsearch::rawSetUp as elasticsearchSetUp;
	}
	use RabbitMq {
		RabbitMq::setUp as rabbitMqSetUp;
	}

	/** @var mixed[] */
	private $configuration;

	protected function setUp(): void {
		parent::setUp();
		$this->configuration = (new Configuration\CombinedSource(
			new FindMyFriends\Configuration\ApplicationConfiguration(),
			new Configuration\ValidIni(
				new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
			)
		))->read();
		$this->databaseSetUp();
		$this->elasticsearchSetUp();
		$this->rabbitMqSetUp();
	}
}
