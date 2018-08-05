<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint;

use FindMyFriends\Configuration;
use FindMyFriends\Domain\Access;
use FindMyFriends\Routing;
use Klapuch\Http;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class InvalidJsonTest extends Tester\TestCase {
	/**
	 * @dataProvider sorts
	 */
	public function testInvalidJsonInput(string $endpoint, string $method) {
		$response = $this->response($endpoint, $method);
		Assert::same(['message' => 'JSON is not valid'], json_decode($response->body(), true));
		Assert::same(HTTP_BAD_REQUEST, $response->code());
	}

	public function testNumberOfNeededChecks() {
		static $ignores = [
			'demands/{demand_id}/soulmate_requests [POST]',
		];
		$routes = preg_grep(
			'~\[POST|PUT|PATCH\]~',
			array_keys((new Routing\TestApplicationRoutes())->matches())
		);
		if (array_intersect($ignores, $routes) !== $ignores)
			Assert::fail('Some of the ignored routes not exists anymore.');
		Assert::same(count($this->sorts()), count(array_diff($routes, $ignores)));
	}

	private function response(string $endpoint, string $method): Http\Response {
		return (new Http\BasicRequest(
			$method,
			new Uri\FakeUri(sprintf('http://find-my-friends-nginx/%s', $endpoint)),
			[
				CURLOPT_HTTPHEADER => [sprintf('Authorization: Bearer %s', $this->token())],
			],
			''
		))->send();
	}

	private function token(): string {
		return (new Access\TestingEntrance())->enter([])->id();
	}

	protected function sorts(): array {
		$config = (new Configuration\ApplicationConfiguration())->read();
		return [
			['demands', 'POST'],
			[sprintf('demands/%s', $config['HASHIDS']['demand']->encode(1)), 'PUT'],
			[sprintf('demands/%s', $config['HASHIDS']['demand']->encode(1)), 'PATCH'],
			['evolutions', 'POST'],
			[sprintf('evolutions/%s', $config['HASHIDS']['evolution']->encode(1)), 'PUT'],
			['seekers', 'POST'],
			['tokens', 'POST'],
			[sprintf('soulmates/%s', $config['HASHIDS']['soulmate']->encode(1)), 'PATCH'],
			[sprintf('evolutions/%s/spots', $config['HASHIDS']['evolution']->encode(1)), 'POST'],
			[sprintf('demands/%s/spots', $config['HASHIDS']['demand']->encode(1)), 'POST'],
			[sprintf('spots/%s', $config['HASHIDS']['spot']->encode(1)), 'PUT'],
			[sprintf('spots/%s', $config['HASHIDS']['spot']->encode(1)), 'PATCH'],
		];
	}
}

(new InvalidJsonTest())->run();
