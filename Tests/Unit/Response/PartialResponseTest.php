<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output\Json;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PartialResponseTest extends Tester\TestCase {
	public function testPartitioningByFieldParameter() {
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

	public function testReturningAllForNoFieldParameter() {
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
