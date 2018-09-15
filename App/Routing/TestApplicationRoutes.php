<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use FindMyFriends\Elasticsearch\LazyElasticsearch;
use FindMyFriends\Misc;
use Hashids\Hashids;
use Klapuch\Encryption;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;

/**
 * Simplified application routes for testing
 */
final class TestApplicationRoutes implements Routing\Routes {
	public function matches(): array {
		return (new ApplicationRoutes(
			new Uri\FakeUri(),
			new class extends Storage\MetaPDO {
				public function __construct() {
				}
			},
			new Misc\FakeRedis(),
			new LazyElasticsearch([]),
			new PhpAmqpLib\Connection\AMQPLazyConnection(
				'',
				'',
				'',
				''
			),
			new Encryption\FakeCipher(),
			[
				'spot' => new Hashids(),
				'demand' => new Hashids(),
				'evolution' => new Hashids(),
				'soulmate' => new Hashids(),
			]
		))->matches();
	}
}
