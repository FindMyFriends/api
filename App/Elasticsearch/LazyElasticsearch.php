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
		if (self::$cache === null) {
			self::$cache = Elasticsearch\ClientBuilder::create()
				->setHosts($this->hosts)
				->build();
		}
		return self::$cache;
	}
}
