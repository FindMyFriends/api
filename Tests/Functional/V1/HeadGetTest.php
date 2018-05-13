<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1;

use FindMyFriends\Routing;
use GuzzleHttp;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HeadGetTest extends Tester\TestCase {
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
						(new Routing\TestApplicationRoutes())->matches()
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
