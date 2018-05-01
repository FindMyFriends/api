<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1;

use FindMyFriends\V1\Demands;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SortTest extends Tester\TestCase {
	/**
	 * @dataProvider sorts
	 */
	public function testAllowedSorts(string $endpoint) {
		$response = $this->response($endpoint);
		Assert::same(HTTP_OK, $response['info']['http_code']);
	}

	private function response(string $endpoint): array {
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, sprintf('http://find-my-friends-nginx/%s', $endpoint));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

	protected function sorts(): array {
		return [
			[sprintf('v1/demands?sort=%s', implode(',', Demands\Get::SORTS))],
		];
	}
}

(new SortTest())->run();
