<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class EmptyResponseTest extends TestCase\Runtime {
	public function testNoResponseCode(): void {
		Assert::same(HTTP_NO_CONTENT, (new Response\EmptyResponse())->status());
	}
}

(new EmptyResponseTest())->run();
