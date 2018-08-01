<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Domain;

use FindMyFriends\Domain;
use FindMyFriends\Domain\Place;
use Hashids\Hashids;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PublicLocationTest extends Tester\TestCase {
	public function testFormatting() {
		Assert::equal(
			[
				'assigned_at' => '2017-09-17T13:58:10+00:00',
				'demand_id' => 'pY',
				'id' => 'RD',
			],
			json_decode(
				(new Domain\PublicLocation(
					new Place\PublicLocation(new Place\FakeLocation(), new Hashids('a')),
					new Hashids('b')
				))->print(
					new Output\Json(
						[
							'id' => 1,
							'demand_id' => 1,
							'assigned_at' => '2017-09-17 13:58:10.531097+00',
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new PublicLocationTest())->run();
