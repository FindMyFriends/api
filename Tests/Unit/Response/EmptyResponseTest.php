<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class EmptyResponseTest extends Tester\TestCase {
	public function testNoResponseCode() {
		Assert::same(HTTP_NO_CONTENT, (new Response\EmptyResponse())->status());
	}
}

(new EmptyResponseTest())->run();
