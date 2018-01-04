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

final class FormattedDemandTest extends Tester\TestCase {
	public function testFormatting() {
		Assert::equal(
			[
				'created_at' => '2017-09-17T13:58:10+00:00',
				'location' => [
					'met_at' => [
						'moment' => '2016-09-17T13:58:10+00:00',
						'approximation' => 'PT10H',
					],
				],
			],
			json_decode(
				(new Domain\FormattedDemand(
					new Domain\FakeDemand()
				))->print(
					new Output\Json(
						[
							'created_at' => '2017-09-17 13:58:10.531097+00',
							'location' => [
								'met_at' => [
									'moment' => '2016-09-17 13:58:10.531097+00',
									'approximation' => 'PT10H',
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

(new FormattedDemandTest())->run();