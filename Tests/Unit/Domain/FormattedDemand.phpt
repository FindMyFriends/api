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

final class FormattedDemand extends Tester\TestCase {
	public function testFormatting() {
		Assert::equal(
			[
				'created_at' => '2017-09-17T13:58:10+00:00',
				'location' => [
					'met_at' => [
						'from' => '2016-09-17T13:58:10+00:00',
						'to' => '2016-10-17T13:58:10+00:00',
					],
				],
				'general' => [
					'age' => ['from' => 20, 'to' => 25],
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
									'from' => '2016-09-17 13:58:10.531097+00',
									'to' => '2016-10-17 13:58:10.531097+00',
								],
							],
							'general' => [
								'age' => ['from' => '20', 'to' => '25'],
							],
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new FormattedDemand())->run();