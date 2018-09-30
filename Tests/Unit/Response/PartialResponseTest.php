<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output\Json;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class PartialResponseTest extends TestCase\Runtime {
	public function testPartitioningByFieldParameter(): void {
		Assert::same(
			['id' => 10],
			json_decode(
				(new Response\PartialResponse(
					new Application\FakeResponse(new Json(['id' => 10, 'name' => 'Dom'])),
					['fields' => 'id']
				))->body()->serialization(),
				true
			)
		);
	}

	public function testReturningAllForNoFieldParameter(): void {
		Assert::same(
			['id' => 10, 'name' => 'Dom'],
			json_decode(
				(new Response\PartialResponse(
					new Application\FakeResponse(new Json(['id' => 10, 'name' => 'Dom'])),
					[]
				))->body()->serialization(),
				true
			)
		);
	}
}

(new PartialResponseTest())->run();
