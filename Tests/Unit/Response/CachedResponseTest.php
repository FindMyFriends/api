<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CachedResponseTest extends TestCase\Runtime {
	use TestCase\Mockery;

	public function testMultipleCallsWithSingleExecution(): void {
		$origin = $this->mock(Application\Response::class);
		$origin->shouldReceive('body')->once();
		$origin->shouldReceive('headers')->once();
		$origin->shouldReceive('status')->once();
		$response = new Response\CachedResponse($origin);
		Assert::equal($response->body(), $response->body());
		Assert::equal($response->headers(), $response->headers());
		Assert::equal($response->status(), $response->status());
	}
}

(new CachedResponseTest())->run();
