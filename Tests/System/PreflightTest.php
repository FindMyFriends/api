<?php
declare(strict_types = 1);

namespace FindMyFriends\System;

use FindMyFriends\Domain\Access;
use FindMyFriends\Routing;
use FindMyFriends\TestCase;
use Klapuch\Http;
use Klapuch\Uri\FakeUri;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class PreflightTest extends TestCase\Runtime {
	/**
	 * @dataProvider preflightHeaders
	 */
	public function testPreflightRequestsByMatchingHeaders(array $requestHeaders): void {
		foreach ($this->endpoints() as $endpoint) {
			$response = $this->response($endpoint, $requestHeaders);
			Assert::same('', $response->body());
			Assert::same(HTTP_NO_CONTENT, $response->code());
			$headers = $response->headers();
			Assert::same('0', $headers['Content-Length']);
			Assert::same('text/plain;charset=UTF-8', $headers['Content-Type']);
			Assert::true(isset($headers['Access-Control-Allow-Methods']));
			Assert::true(isset($headers['Access-Control-Allow-Origin']));
			Assert::true(isset($headers['Access-Control-Allow-Headers']));
		}
	}

	public function testDomainOptions(): void {
		$token = $this->token();
		foreach ($this->endpoints() as $endpoint) {
			$response = $this->response($endpoint, [sprintf('Authorization: Bearer %s', $token)]);
			Assert::same(HTTP_OK, $response->code());
			Assert::notSame([], json_decode($response->body()));
			$headers = $response->headers();
			Assert::same('application/json; charset=utf8', $headers['Content-Type']);
		}
	}

	private function token(): string {
		return (new Access\TestingEntrance())->enter([])->id();
	}

	private function endpoints(): array {
		$matches = (new Routing\TestApplicationRoutes())->matches();
		return str_replace(' [OPTIONS]', '', preg_grep('~^\w+ \[OPTIONS\]$~', array_keys($matches)));
	}

	private function response(string $endpoint, array $headers = []): Http\Response {
		return (new Http\BasicRequest(
			'OPTIONS',
			new FakeUri(sprintf('http://find-my-friends-nginx/%s', $endpoint)),
			[CURLOPT_HTTPHEADER => $headers]
		))->send();
	}

	protected function preflightHeaders(): array {
		return [
			[
				[
					'Access-Control-Request-Method: POST',
					'Access-Control-Request-Headers: Authorization',
					'Origin: http://find-my-friends-nginx',
				],
			],
			[
				[
					'access-control-request-method: POST',
					'access-control-request-headers: Authorization',
					'origin: http://find-my-friends-nginx',
				],
			],
		];
	}
}

(new PreflightTest())->run();
