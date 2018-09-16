<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Http;
use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ConcurrentlyCreatedResponseTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testStoredETag() {
		$eTag = $this->mock(Http\ETag::class);
		$eTag->shouldReceive('set')->once();
		Assert::same(
			['Accept' => 'text/html'],
			(new Response\ConcurrentlyCreatedResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['Accept' => 'text/html']),
				$eTag
			))->headers()
		);
	}
}

(new ConcurrentlyCreatedResponseTest())->run();
