<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedResponseTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testMultipleCallsWithSingleExecution() {
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