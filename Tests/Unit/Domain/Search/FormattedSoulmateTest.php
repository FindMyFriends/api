<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Domain\Search;

use FindMyFriends\Domain\Search;
use Hashids\Hashids;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class FormattedSoulmateTest extends Tester\TestCase {
	public function testFormatting() {
		Assert::equal(
			[
				'id' => '0E',
				'demand_id' => 'RD',
				'evolution_id' => 'pY',
				'seeker_id' => 1,
			],
			json_decode(
				(new Search\FormattedSoulmate(
					new Search\FakeSoulmate(),
					[
						'demand' => ['hashid' => new Hashids('a')],
						'evolution' => ['hashid' => new Hashids('b')],
						'soulmate' => ['hashid' => new Hashids('c')],
					]
				))->print(
					new Output\Json(
						[
							'id' => 1,
							'demand_id' => 1,
							'evolution_id' => 1,
							'seeker_id' => 1,
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new FormattedSoulmateTest())->run();