<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Routing;

use FindMyFriends\Routing;
use Klapuch\Routing\FakeRoutes;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class NginxMatchedRoutesTest extends Tester\TestCase {
	public function testMatchingByServer() {
		$_SERVER['ROUTE_NAME'] = 'demands/{id}';
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		Assert::same(
			['demands/{id}' => 'foo'],
			(new Routing\NginxMatchedRoutes(
				new FakeRoutes(['demands/{id} [PUT]' => 'foo'])
			))->matches()
		);
	}

	public function testNoMatchAsEmpty() {
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
