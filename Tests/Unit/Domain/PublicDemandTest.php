<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Domain;

use FindMyFriends\Domain;
use Hashids\Hashids;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PublicDemandTest extends Tester\TestCase {
	public function testFormatting() {
		Assert::equal(
			[
				'id' => 'jR',
				'created_at' => '2017-09-17T13:58:10+00:00',
			],
			json_decode(
				(new Domain\PublicDemand(
					new Domain\FakeDemand(),
					new Hashids()
				))->print(
					new Output\Json(
						[
							'id' => 1,
							'created_at' => '2017-09-17 13:58:10.531097+00',
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new PublicDemandTest())->run();
