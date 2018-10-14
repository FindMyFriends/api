<?php
declare(strict_types = 1);

namespace FindMyFriends\System;

use FindMyFriends\Domain\Access;
use FindMyFriends\Routing;
use FindMyFriends\TestCase;
use Klapuch\Http;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class HeadGetTest extends TestCase\Runtime {
	/**
	 * @dataProvider getHeadEndpoints
	 */
	public function testHeadAndGetMatchingInHeaders(string $endpoint): void {
		$headResponse = $this->response($endpoint, 'HEAD');
		$getResponse = $this->response($endpoint, 'GET');
		$headHeaders = $headResponse->headers();
		$getHeaders = $getResponse->headers();
		Assert::same(200, $headResponse->code());
		Assert::same(200, $getResponse->code());
		unset($headHeaders['Content-Type'], $getHeaders['Content-Type']);
		unset($headHeaders['Content-Length'], $getHeaders['Content-Length']);
		unset($headHeaders['Date'], $getHeaders['Date']);
		unset($getHeaders['Transfer-Encoding']);
		Assert::same($headHeaders, $getHeaders);
	}

	public function testNumberOfGetHeadForTest(): void {
		Assert::same(
			count($this->getHeadEndpoints()),
			count(
				preg_grep(
					'~\[HEAD\]~',
					array_keys(
						(new Routing\TestApplicationRoutes())->matches()
					)
				)
			)
		);
	}

	private function response(string $endpoint, string $method): Http\Response {
		return (new Http\BasicRequest(
			$method,
			new Uri\FakeUri(sprintf('http://find-my-friends-nginx/%s', $endpoint)),
			[
				CURLOPT_NOBODY => true,
				CURLOPT_HTTPHEADER => [sprintf('Authorization: Bearer %s', $this->token())],
			]
		))->send();
	}

	private function token(): string {
		return (new Access\TestingEntrance())->enter([])->id();
	}

	protected function getHeadEndpoints(): array {
		return [
			['soulmates'],
			['notifications'],
		];
	}
}

(new HeadGetTest())->run();
