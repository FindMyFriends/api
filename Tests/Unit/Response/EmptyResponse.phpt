<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class EmptyResponse extends \Tester\TestCase {
	public function testNoResponseCode() {
		Assert::same(204, (new Response\EmptyResponse())->status());
	}
}

(new EmptyResponse())->run();