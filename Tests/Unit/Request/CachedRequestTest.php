<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Request;

use FindMyFriends\Request;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CachedRequestTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testMultipleCallsWithSingleExecution() {
		$origin = $this->mock(Application\Request::class);
		$origin->shouldReceive('body')->once();
		$origin->shouldReceive('headers')->once();
		$response = new Request\CachedRequest($origin);
		Assert::equal($response->body(), $response->body());
		Assert::equal($response->headers(), $response->headers());
	}
}

(new CachedRequestTest())->run();
