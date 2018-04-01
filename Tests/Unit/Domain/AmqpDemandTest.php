<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Domain;

use FindMyFriends\Domain;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AmqpDemandTest extends Tester\TestCase {
	public function testKeepingRelevant() {
		Assert::equal(
			[
				'id' => 2,
				'seeker_id' => '666',
				'request_id' => 123,
			],
			json_decode(
				(new Domain\AmqpDemand(
					new Domain\FakeDemand()
				))->print(
					new Output\Json(
						[
							'id' => 2,
							'seeker_id' => '666',
							'request_id' => 123,
							'foo' => 'bar',
							'nested' => [
								'foo' => [
									'baz' => 'bar',
								],
							],
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new AmqpDemandTest())->run();