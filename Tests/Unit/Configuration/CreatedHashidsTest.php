<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Configuration;

use FindMyFriends\Configuration;
use Hashids\Hashids;
use Klapuch\Configuration\FakeSource;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CreatedHashidsTest extends Tester\TestCase {
	public function testCreatingWithSaltAndLength() {
		Assert::equal(
			[
				'demand' => new Hashids('abc', 10),
				'evolution' => new Hashids('def', 15),
			],
			(new Configuration\CreatedHashids(
				new FakeSource(
					[
						'demand' => ['length' => 10, 'salt' => 'abc'],
						'evolution' => ['length' => 15, 'salt' => 'def'],
					]
				)
			))->read()
		);
	}
}

(new CreatedHashidsTest())->run();
