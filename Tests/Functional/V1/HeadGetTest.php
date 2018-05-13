<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1;

use Elasticsearch;
use FindMyFriends\Routing;
use FindMyFriends\TestCase;
use GuzzleHttp;
use Hashids\Hashids;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HeadGetTest extends Tester\TestCase {
	use TestCase\Redis;

	/**
	 * @dataProvider getHeadEndpoints
	 */
	public function testHeadAndGetMatchingInHeaders(string $endpoint) {
		$headHeaders = $this->response($endpoint, 'HEAD');
		$getHeaders = $this->response($endpoint, 'GET');
		unset($headHeaders['Content-Type'], $getHeaders['Content-Type']);
		unset($getHeaders['Transfer-Encoding']);
		Assert::same($headHeaders, $getHeaders);
	}

	public function testNumberOfGetHeadForTest() {
		Assert::same(
			count($this->getHeadEndpoints()),
			count(
				preg_grep(
					'~\[HEAD\]~',
					array_keys(
						(new Routing\ApplicationRoutes(
							new Uri\FakeUri(),
							new class extends Storage\MetaPDO {
								public function __construct() {
								}
							},
							$this->redis,
							Elasticsearch\ClientBuilder::create()->build(),
							new PhpAmqpLib\Connection\AMQPLazyConnection(
								'',
								'',
								'',
								''
							),
							[
								'demand' => ['hashid' => new Hashids()],
								'evolution' => ['hashid' => new Hashids()],
								'soulmate' => ['hashid' => new Hashids()],
							]
						))->matches()
					)
				)
			)
		);
	}

	private function response(string $endpoint, string $method): array {
		return (new GuzzleHttp\Client())->request(
			$method,
			sprintf('http://find-my-friends-nginx/%s', $endpoint)
		)->getHeaders();
	}

	protected function getHeadEndpoints(): array {
		return [
			['v1/demands/2wrWlWqMg7DY/soulmates'],
		];
	}
}

(new HeadGetTest())->run();
