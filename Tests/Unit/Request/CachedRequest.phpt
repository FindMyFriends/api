<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Request;

use FindMyFriends\Request;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedRequest extends Tester\TestCase {
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

(new CachedRequest())->run();