<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Interaction;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PublicDemandTest extends TestCase\Runtime {
	public function testFormatting(): void {
		Assert::equal(
			[
				'id' => 'jR',
				'created_at' => '2017-09-17T13:58:10+00:00',
			],
			json_decode(
				(new Interaction\PublicDemand(
					new Interaction\FakeDemand(),
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
