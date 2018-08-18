<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use FindMyFriends\Elasticsearch\LazyElasticsearch;
use Klapuch\Configuration;
use Tester;

trait Elasticsearch {
	/** @var \Elasticsearch\Client */
	protected $elasticsearch;

	/** @var \FindMyFriends\Elasticsearch\LazyElasticsearch */
	protected $lazyElasticsearch;

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
		$this->lazyElasticsearch = new LazyElasticsearch($credentials['ELASTICSEARCH']['hosts']);
		$this->elasticsearch = $this->lazyElasticsearch->create();
	}
}
