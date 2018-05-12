<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1;

use Elasticsearch;
use FindMyFriends\Routing;
use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use FindMyFriends\V1\Demand;
use FindMyFriends\V1\Demands;
use Hashids\Hashids;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SortTest extends Tester\TestCase {
	use TestCase\Redis;

	/**
	 * @dataProvider sorts
	 */
	public function testAllowedSorts(string $endpoint) {
		$response = $this->response($endpoint);
		Assert::same(HTTP_OK, $response['info']['http_code']);
	}

	public function testNumberOfSortsForTest() {
		Assert::same(
			count($this->sorts()),
			count(
				preg_grep(
					'~sort=~',
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
