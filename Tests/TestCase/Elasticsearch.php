<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Elasticsearch\ClientBuilder;
use Klapuch\Configuration;
use Tester;

trait Elasticsearch {
	/** @var \Elasticsearch\Client */
	protected $elasticsearch;

	/** @var mixed[] */
	protected $credentials;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('elasticsearch', __DIR__ . '/../temp');
		$this->credentials = (new Configuration\ValidIni(
			new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
		))->read();
		$this->elasticsearch = ClientBuilder::create()
			->setHosts($this->credentials['ELASTICSEARCH']['hosts'])
			->build();
		$this->elasticsearch->indices()->delete(['index' => '*']);
	}
}