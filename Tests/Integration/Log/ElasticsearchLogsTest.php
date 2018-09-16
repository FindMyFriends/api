<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Log;

use FindMyFriends\Log;
use FindMyFriends\TestCase;
use Klapuch\Log\CurrentEnvironment;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class ElasticsearchLogsTest extends Tester\TestCase {
	use TestCase\Elasticsearch;

	public function testStoringOnPile() {
		(new Log\ElasticsearchLogs(
			$this->lazyElasticsearch
		))->put(new \RuntimeException('Ooops'), new CurrentEnvironment());
		sleep(1);
		$response = $this->elasticsearch->search(['index' => 'logs', 'type' => 'pile']);
		Assert::same(1, $response['hits']['total']);
	}
}

(new ElasticsearchLogsTest())->run();
