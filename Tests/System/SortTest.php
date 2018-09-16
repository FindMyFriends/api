<?php
declare(strict_types = 1);

namespace FindMyFriends\System;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint\Demand;
use FindMyFriends\Endpoint\Demands;
use FindMyFriends\Endpoint\Evolutions;
use FindMyFriends\Schema;
use Klapuch\Dataset;
use Klapuch\Http;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class SortTest extends Tester\TestCase {
	/**
	 * @dataProvider sorts
	 */
	public function testAllowedSorts(string $endpoint) {
		$response = $this->response($endpoint);
		Assert::same('[]', $response->body());
		Assert::same(HTTP_OK, $response->code());
	}

	public function testNumberOfSorts() {
		if (!class_exists(Dataset\RestSort::class))
			Assert::fail('Class RestSort is no longer exist and is not possible to check sort occurrence');
		Assert::same(
			count($this->sorts()),
			iterator_count(
				new \CallbackFilterIterator(
					new \RecursiveIteratorIterator(
						new \RecursiveDirectoryIterator(
							__DIR__ . '/../../App/Endpoint'
						)
					),
					static function (\SplFileInfo $file): bool {
						return strpos(file_get_contents($file->getPathname()), 'new Dataset\RestSort(') !== false;
					}
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
		return (new Access\TestingEntrance())->enter([])->id();
	}

	protected function sorts(): array {
		return [
			[
				sprintf('demands?sort=%s', implode(',', Demands\Get::SORTS)),
			],
			[
				sprintf('evolutions?sort=%s', implode(',', Evolutions\Get::SORTS)),
			],
			[
				sprintf(
					'demands/2wrWlWqMg7DY/soulmate_requests?sort=%s',
					$this->query(Demand\SoulmateRequests\Get::SCHEMA)
				),
			],
			[
				sprintf(
					'demands/2wrWlWqMg7DY/soulmates?sort=%s',
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
