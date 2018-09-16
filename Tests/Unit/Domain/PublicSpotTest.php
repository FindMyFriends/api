<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Place;
use Hashids\Hashids;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class PublicSpotTest extends Tester\TestCase {
	public function testFormatting() {
		Assert::equal(
			[
				'assigned_at' => '2017-09-17T13:58:10+00:00',
				'demand_id' => 'pY',
				'id' => 'RD',
			],
			json_decode(
				(new Interaction\PublicSpot(
					new Place\PublicSpot(new Place\FakeSpot(), new Hashids('a')),
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

(new PublicSpotTest())->run();
