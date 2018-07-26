<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Elasticsearch\ClientBuilder;
use Klapuch\Configuration;
use Tester;

trait Elasticsearch {
	/** @var \Elasticsearch\Client */
	protected $elasticsearch;

	protected function setUp(): void {
		$this->rawSetUp();
		$this->elasticsearch->indices()->delete(['index' => '*']);
	}

	protected function rawSetUp(): void {
		parent::setUp();
		Tester\Environment::lock('elasticsearch', __DIR__ . '/../temp');
		$credentials = (new Configuration\ValidIni(
			new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
		))->read();
		$this->elasticsearch = ClientBuilder::create()
			->setHosts($credentials['ELASTICSEARCH']['hosts'])
			->build();
	}
}
