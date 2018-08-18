<?php
declare(strict_types = 1);

namespace FindMyFriends\Elasticsearch;

use Elasticsearch;

final class LazyElasticsearch {
	/** @var mixed[] */
	private $hosts;

	/** @var \Elasticsearch\Client */
	private static $cache;

	public function __construct(array $hosts) {
		$this->hosts = $hosts;
	}

	public function create(): Elasticsearch\Client {
		if (static::$cache === null) {
			static::$cache = Elasticsearch\ClientBuilder::create()
				->setHosts($this->hosts)
				->build();
		}
		return static::$cache;
	}
}
