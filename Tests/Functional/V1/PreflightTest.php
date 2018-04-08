<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1;

use FindMyFriends\Routing;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Storage;
use Klapuch\Uri;
use Predis;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PreflightTest extends Tester\TestCase {
	use TestCase\Page;

	/**
	 * @dataProvider preflightHeaders
	 */
	public function testPreflightRequestsByMatchingHeaders(array $headers) {
		foreach ($this->endpoints() as $endpoint) {
			$response = $this->response($endpoint, $headers);
			Assert::same(HTTP_NO_CONTENT, $response['info']['http_code']);
			Assert::same('', $response['body']);
			Assert::contains('Content-Length: 0', $response['headers']);
			Assert::contains('Content-Type: text/plain;charset=UTF-8', $response['headers']);
			Assert::contains('Access-Control-Allow-Methods: ', $response['headers']);
			Assert::contains('Access-Control-Allow-Origin: ', $response['headers']);
			Assert::contains('Access-Control-Allow-Headers: ', $response['headers']);
		}
	}

	public function testDomainOptions() {
		foreach ($this->endpoints() as $endpoint) {
			$response = $this->response($endpoint);
			Assert::same(HTTP_OK, $response['info']['http_code']);
			Assert::notSame([], json_decode($response['body'], true));
			Assert::notContains('Content-Length: 0', $response['headers']);
			Assert::contains('Content-Type: application/json; charset=utf8', $response['headers']);
		}
	}

	private function endpoints(): array {
		$matches = (new Routing\ApplicationRoutes(
			new Uri\FakeUri(),
			new class extends Storage\MetaPDO {
				public function __construct() {
				}
			},
			new Predis\Client(),
			$this->elasticsearch,
			$this->rabbitMq,
			[
				'demand' => ['hashid' => new Hashids()],
				'evolution' => ['hashid' => new Hashids()],
				'soulmate' => ['hashid' => new Hashids()],
			]
		))->matches();
		return str_replace('[OPTIONS]', '', preg_grep('~^v1/\w+ \[OPTIONS\]$~', array_keys($matches)));
	}

	private function response(string $endpoint, array $headers = []): array {
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, sprintf('http://find-my-friends-nginx/%s', $endpoint));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			Assert::same('', curl_error($ch));
			return [
				'headers' => substr($response, 0, $headerSize),
				'body' => substr($response, $headerSize),
				'info' => curl_getinfo($ch),
			];
		} finally {
			curl_close($ch);
		}
	}

	protected function preflightHeaders(): array {
		return [
			[[
				'Access-Control-Request-Method: POST',
				'Access-Control-Request-Headers: Authorization',
				'Origin: http://find-my-friends-nginx',
			]],
			[[
				'access-control-request-method: POST',
				'access-control-request-headers: Authorization',
				'origin: http://find-my-friends-nginx',
			]],
		];
	}
}

(new PreflightTest())->run();
