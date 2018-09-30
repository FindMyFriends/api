<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Interaction;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Place;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PublicSpotTest extends TestCase\Runtime {
	public function testFormatting(): void {
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
