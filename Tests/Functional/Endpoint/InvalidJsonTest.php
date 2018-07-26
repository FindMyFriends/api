<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint;

use FindMyFriends\Configuration;
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
		session_start();
		$_SESSION['id'] = '1';
		$sessionId = session_id();
		chown(sprintf('/tmp/sess_%s', $sessionId), 'www-data');
		session_write_close();
		return $sessionId;
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
			[sprintf('evolutions/%s/locations', $config['HASHIDS']['evolution']->encode(1)), 'POST'],
		];
	}
}

(new InvalidJsonTest())->run();
