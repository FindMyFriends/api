<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1;

use FindMyFriends\Routing;
use FindMyFriends\Schema;
use FindMyFriends\V1\Demand;
use FindMyFriends\V1\Demands;
use Klapuch\Http;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SortTest extends Tester\TestCase {
	/**
	 * @dataProvider sorts
	 */
	public function testAllowedSorts(string $endpoint) {
		$response = $this->response($endpoint);
		Assert::same('[]', $response->body());
		Assert::same(HTTP_OK, $response->code());
	}

	public function testNumberOfSortsForTest() {
		Assert::same(
			count($this->sorts()),
			count(
				preg_grep(
					'~sort=~',
					array_keys(
						(new Routing\TestApplicationRoutes())->matches()
					)
				)
			)
		);
	}

	private function response(string $endpoint): Http\Response {
		return (new Http\BasicRequest(
			'GET',
			new Uri\FakeUri(sprintf('http://find-my-friends-nginx/%s', $endpoint)),
			[
				CURLOPT_HTTPHEADER => [sprintf('Authorization: Bearer %s', $this->token())],
			]
		))->send();
	}

	private function token(): string {
		session_start();
		$_SESSION['id'] = '1';
		$sessionId = session_id();
		chown(sprintf('/tmp/sess_%s', $sessionId), 'www-data');
		session_write_close();
		return $sessionId;
	}

	protected function sorts(): array {
		return [
			[
				sprintf('v1/demands?sort=%s', implode(',', Demands\Get::SORTS)),
			],
			[
				sprintf(
					'v1/demands/2wrWlWqMg7DY/soulmate_requests?sort=%s',
					$this->query(Demand\SoulmateRequests\Get::SCHEMA)
				),
			],
			[
				sprintf(
					'v1/demands/2wrWlWqMg7DY/soulmates?sort=%s',
					$this->query(Demand\Soulmates\Get::SCHEMA)
				),
			],
		];
	}

	/**
	 * @internal
	 */
	private function query(string $schema): string {
		return implode(
			',',
			(new Schema\NestedProperties(
				new Schema\JsonProperties(new \SplFileInfo($schema))
			))->objects()
		);
	}
}

(new SortTest())->run();
