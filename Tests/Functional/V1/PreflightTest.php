<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1;

use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PreflightTest extends Tester\TestCase {
	use TestCase\Page;

	private const ENDPOINTS = [
		'v1/demands',
		'v1/evolutions',
	];

	/**
	 * @dataProvider preflightHeaders
	 */
	public function testPreflightRequestsByMatchingHeaders(array $headers) {
		foreach (self::ENDPOINTS as $endpoint) {
			$response = $this->response($endpoint, $headers);
			Assert::same('', $response['error']);
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
		foreach (self::ENDPOINTS as $endpoint) {
			$response = $this->response($endpoint);
			Assert::same('', $response['error']);
			Assert::same(HTTP_OK, $response['info']['http_code']);
			Assert::notSame([], json_decode($response['body'], true));
			Assert::notContains('Content-Length: 0', $response['headers']);
			Assert::contains('Content-Type: application/json; charset=utf8', $response['headers']);
		}
	}

	public function response(string $endpoint, array $headers = []): array {
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, sprintf('http://172.18.0.2/%s', $endpoint));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			return [
				'headers' => substr($response, 0, $headerSize),
				'body' => substr($response, $headerSize),
				'error' => curl_error($ch),
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
				'Origin: http://172.18.0.2',
			]],
			[[
				'access-control-request-method: POST',
				'access-control-request-headers: Authorization',
				'origin: http://172.18.0.2',
			]],
		];
	}
}

(new PreflightTest())->run();