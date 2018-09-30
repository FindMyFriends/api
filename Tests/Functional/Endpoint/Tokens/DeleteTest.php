<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Tokens;

use FindMyFriends\Endpoint;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DeleteTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		$response = (new Endpoint\Tokens\Delete())->response([]);
		Assert::null(json_decode($response->body()->serialization(), true));
		Assert::same(HTTP_OK, $response->status());
	}
}

(new DeleteTest())->run();
