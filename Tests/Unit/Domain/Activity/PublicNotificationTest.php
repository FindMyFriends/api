<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Activity;

use FindMyFriends\Domain\Activity;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PublicNotificationTest extends TestCase\Runtime {
	public function testFormatting(): void {
		Assert::equal(
			[
				'seen_at' => '2017-09-17T13:58:10+00:00',
				'notified_at' => '2018-09-17T13:58:10+00:00',
			],
			json_decode(
				(new Activity\PublicNotification(
					new Activity\FakeNotification()
				))->receive(
					new Output\Json(
						[
							'seen_at' => '2017-09-17 13:58:10.531097+00',
							'notified_at' => '2018-09-17 13:58:10.531097+00',
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
				'seen_at' => null,
				'notified_at' => '2018-09-17T13:58:10+00:00',
			],
			json_decode(
				(new Activity\PublicNotification(
					new Activity\FakeNotification()
				))->receive(
					new Output\Json(
						[
							'seen_at' => null,
							'notified_at' => '2018-09-17 13:58:10.531097+00',
						]
					)
				)->serialization(),
				true
			)
		);
	}
}

(new PublicNotificationTest())->run();
