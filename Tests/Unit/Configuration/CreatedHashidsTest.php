<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Configuration;

use FindMyFriends\Configuration;
use Hashids\Hashids;
use Klapuch\Configuration\FakeSource;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CreatedHashidsTest extends Tester\TestCase {
	public function testCreatingWithSaltAndLength() {
		Assert::equal(
			[
				'demand' => ['hashid' => new Hashids('abc', 10)],
				'evolution' => ['hashid' => new Hashids('def', 15)],
			],
			(new Configuration\CreatedHashids(
				new FakeSource(
					[
						'demand' => ['hashid' => ['length' => 10, 'salt' => 'abc']],
						'evolution' => ['hashid' => ['length' => 15, 'salt' => 'def']],
					]
				)
			))->read()
		);
	}

	public function testAddingAdditionalKeys() {
		Assert::equal(
			[
				'demand' => ['hashid' => new Hashids('abc', 10), 'paths' => ['foo']],
				'evolution' => ['hashid' => new Hashids('def', 15), 'paths' => ['bar']],
			],
			(new Configuration\CreatedHashids(
				new FakeSource(
					[
						'demand' => ['hashid' => ['length' => 10, 'salt' => 'abc'], 'paths' => ['foo']],
						'evolution' => ['hashid' => ['length' => 15, 'salt' => 'def'], 'paths' => ['bar']],
					]
				)
			))->read()
		);
	}
}

(new CreatedHashidsTest())->run();
