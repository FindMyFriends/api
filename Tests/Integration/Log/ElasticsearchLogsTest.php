<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Log;

use FindMyFriends\Log;
use FindMyFriends\TestCase;
use Klapuch\Log\CurrentEnvironment;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ElasticsearchLogsTest extends Tester\TestCase {
	use TestCase\Elasticsearch;

	public function testStoringOnPile() {
		(new Log\ElasticsearchLogs(
			$this->elasticsearch
		))->put(new \RuntimeException('Ooops'), new CurrentEnvironment());
		sleep(1);
		$response = $this->elasticsearch->search(['index' => 'logs', 'type' => 'pile']);
		Assert::same(1, $response['hits']['total']);
	}
}

(new ElasticsearchLogsTest())->run();
