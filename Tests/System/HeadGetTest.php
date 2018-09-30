<?php
declare(strict_types = 1);

namespace FindMyFriends\System;

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
		$headHeaders = $this->response($endpoint, 'HEAD');
		$getHeaders = $this->response($endpoint, 'GET');
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

	private function response(string $endpoint, string $method): array {
		return (new Http\BasicRequest(
			$method,
			new Uri\FakeUri(sprintf('http://find-my-friends-nginx/%s', $endpoint)),
			[CURLOPT_NOBODY => true]
		))->send()->headers();
	}

	protected function getHeadEndpoints(): array {
		return [
			['demands/2wrWlWqMg7DY/soulmates'],
		];
	}
}

(new HeadGetTest())->run();
