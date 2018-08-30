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
	private const AUTHORIZED = true;

	/**
	 * @dataProvider endpoints
	 */
	public function testInvalidJsonInput(string $endpoint, string $method, bool $authorized) {
		$response = $this->response($endpoint, $method, $authorized);
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
		Assert::same(count($this->endpoints()), count(array_diff($routes, $ignores)));
	}

	private function response(string $endpoint, string $method, bool $authorized): Http\Response {
		return (new Http\BasicRequest(
			$method,
			new Uri\FakeUri(sprintf('http://find-my-friends-nginx/%s', $endpoint)),
			[
				CURLOPT_HTTPHEADER => [
					$authorized
						? sprintf('Authorization: Bearer %s', (new Access\TestingEntrance())->enter([])->id())
						: null,
					'If-None-Match: "abc"',
				],
			],
			''
		))->send();
	}

	protected function endpoints(): array {
		$config = (new Configuration\ApplicationConfiguration())->read();
		return [
			['activations', 'POST', !self::AUTHORIZED],
			['demands', 'POST', self::AUTHORIZED],
			[sprintf('demands/%s', $config['HASHIDS']['demand']->encode(1)), 'PUT', self::AUTHORIZED],
			[sprintf('demands/%s', $config['HASHIDS']['demand']->encode(1)), 'PATCH', self::AUTHORIZED],
			['evolutions', 'POST', self::AUTHORIZED],
			[sprintf('evolutions/%s', $config['HASHIDS']['evolution']->encode(1)), 'PUT', self::AUTHORIZED],
			['seekers', 'POST', self::AUTHORIZED],
			['tokens', 'POST', self::AUTHORIZED],
			[sprintf('soulmates/%s', $config['HASHIDS']['soulmate']->encode(1)), 'PATCH', self::AUTHORIZED],
			[sprintf('evolutions/%s/spots', $config['HASHIDS']['evolution']->encode(1)), 'POST', self::AUTHORIZED],
			[sprintf('demands/%s/spots', $config['HASHIDS']['demand']->encode(1)), 'POST', self::AUTHORIZED],
			[sprintf('spots/%s', $config['HASHIDS']['spot']->encode(1)), 'PUT', self::AUTHORIZED],
			[sprintf('spots/%s', $config['HASHIDS']['spot']->encode(1)), 'PATCH', self::AUTHORIZED],
		];
	}
}

(new InvalidJsonTest())->run();
