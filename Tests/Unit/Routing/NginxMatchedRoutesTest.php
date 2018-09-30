<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Routing;

use FindMyFriends\Routing;
use FindMyFriends\TestCase;
use Klapuch\Routing\FakeRoutes;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class NginxMatchedRoutesTest extends TestCase\Runtime {
	public function testMatchingByServer(): void {
		$_SERVER['ROUTE_NAME'] = 'demands/{id}';
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		Assert::same(
			['demands/{id}' => 'foo'],
			(new Routing\NginxMatchedRoutes(
				new FakeRoutes(['demands/{id} [PUT]' => 'foo'])
			))->matches()
		);
	}

	public function testNoMatchAsEmpty(): void {
		$_SERVER['ROUTE_NAME'] = 'XXXXXXXX';
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		Assert::same(
			[],
			(new Routing\NginxMatchedRoutes(
				new FakeRoutes(['demands/{id} [PUT]' => 'foo'])
			))->matches()
		);
	}
}

(new NginxMatchedRoutesTest())->run();
