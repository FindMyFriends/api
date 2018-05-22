<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Tokens;

use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class DeleteTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		$response = (new V1\Tokens\Delete())->response([]);
		Assert::null(json_decode($response->body()->serialization(), true));
		Assert::same(HTTP_OK, $response->status());
	}
}

(new DeleteTest())->run();
