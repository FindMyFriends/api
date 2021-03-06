<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PublicSoulmateTest extends TestCase\Runtime {
	public function testFormatting(): void {
		Assert::equal(
			[
				'id' => '0E',
				'demand_id' => 'RD',
				'evolution_id' => 'pY',
				'seeker_id' => 1,
				'related_at' => '2017-09-17T13:58:10+00:00',
				'searched_at' => '2018-09-17T13:58:10+00:00',
			],
			json_decode(
				(new Search\PublicSoulmate(
					new Search\FakeSoulmate(),
					[
						'demand' => new Hashids('a'),
						'evolution' => new Hashids('b'),
						'soulmate' => new Hashids('c'),
					]
				))->print(
					new Output\Json(
						[
							'id' => 1,
							'demand_id' => 1,
							'evolution_id' => 1,
							'seeker_id' => 1,
							'related_at' => '2017-09-17 13:58:10.531097+00',
							'searched_at' => '2018-09-17 13:58:10.531097+00',
						]
					)
				)->serialization(),
				true
			)
		);
	}

	public function testHandlingNulls(): void {
		Assert::equal(
			[
				'id' => null,
				'demand_id' => 'RD',
				'evolution_id' => null,
				'seeker_id' => 1,
				'related_at' => null,
				'searched_at' => '2018-09-17T13:58:10+00:00',
			],
			json_decode(
				(new Search\PublicSoulmate(
					new Search\FakeSoulmate(),
					[
						'demand' => new Hashids('a'),
						'evolution' => new Hashids('b'),
						'soulmate' => new Hashids('c'),
					]
				))->print(
					new Output\Json(
						[
							'id' => null,
							'demand_id' => 1,
							'evolution_id' => null,
							'seeker_id' => 1,
							'related_at' => null,
							'searched_at' => '2018-09-17 13:58:10.531097+00',
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new PublicSoulmateTest())->run();
