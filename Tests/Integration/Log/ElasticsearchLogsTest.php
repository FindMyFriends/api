<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Log;

use FindMyFriends\Log;
use FindMyFriends\TestCase;
use Klapuch\Log\CurrentEnvironment;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ElasticsearchLogsTest extends TestCase\Runtime {
	use TestCase\Elasticsearch;

	public function testStoringOnPile(): void {
		(new Log\ElasticsearchLogs(
			$this->lazyElasticsearch
		))->put(new \RuntimeException('Ooops'), new CurrentEnvironment());
		sleep(1);
		$response = $this->elasticsearch->search(['index' => 'logs', 'type' => 'pile']);
		Assert::same(1, $response['hits']['total']);
	}
}

(new ElasticsearchLogsTest())->run();
