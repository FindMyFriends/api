<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Tokens;

use FindMyFriends\Endpoint;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class DeleteTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		$response = (new Endpoint\Tokens\Delete())->response([]);
		Assert::null(json_decode($response->body()->serialization(), true));
		Assert::same(HTTP_OK, $response->status());
	}
}

(new DeleteTest())->run();
