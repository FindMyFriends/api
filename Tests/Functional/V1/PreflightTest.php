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
use Psr\Http\Message;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PreflightTest extends Tester\TestCase {
	use TestCase\Redis;

	/**
	 * @dataProvider preflightHeaders
	 */
	public function testPreflightRequestsByMatchingHeaders(array $requestHeaders) {
		foreach ($this->endpoints() as $endpoint) {
			$response = $this->response($endpoint, $requestHeaders);
			Assert::same('', (string) $response->getBody());
			Assert::same(HTTP_NO_CONTENT, $response->getStatusCode());
			$headers = $response->getHeaders();
			Assert::same(['0'], $headers['Content-Length']);
			Assert::same(['text/plain;charset=UTF-8'], $headers['Content-Type']);
			Assert::true(isset($headers['Access-Control-Allow-Methods']));
			Assert::true(isset($headers['Access-Control-Allow-Origin']));
			Assert::true(isset($headers['Access-Control-Allow-Headers']));
		}
	}

	public function testDomainOptions() {
		foreach ($this->endpoints() as $endpoint) {
			$response = $this->response($endpoint);
			Assert::same(HTTP_OK, $response->getStatusCode());
			Assert::notSame([], json_decode((string) $response->getBody()));
			$headers = $response->getHeaders();
			Assert::same(['application/json; charset=utf8'], $headers['Content-Type']);
		}
	}

	private function endpoints(): array {
		$matches = (new Routing\ApplicationRoutes(
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
		))->matches();
		return str_replace(' [OPTIONS]', '', preg_grep('~^v1/\w+ \[OPTIONS\]$~', array_keys($matches)));
	}

	private function response(string $endpoint, array $headers = []): Message\ResponseInterface {
		return (new GuzzleHttp\Client())->request(
			'OPTIONS',
			sprintf('http://find-my-friends-nginx/%s', $endpoint),
			['headers' => $headers]
		);
	}

	protected function preflightHeaders(): array {
		return [
			[
				[
					'Access-Control-Request-Method' => 'POST',
					'Access-Control-Request-Headers' => 'Authorization',
					'Origin' => 'http://find-my-friends-nginx',
				],
			],
			[
				[
					'access-control-request-method' => 'POST',
					'access-control-request-headers' => 'Authorization',
					'origin' => 'http://find-my-friends-nginx',
				],
			],
		];
	}
}

(new PreflightTest())->run();
