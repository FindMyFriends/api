<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class SeekerRuleTest extends TestCase\Runtime {
	public function testAtLeastOneNotNullContact(): void {
		$subject = [
			'contact' => [
				'facebook' => 'klapuchdominik',
				'instagram' => null,
				'phone_number' => null,
			],
		];
		Assert::same($subject, (new Constraint\SeekerRule())->apply($subject));
	}

	public function testThrowingOnAllNull(): void {
		Assert::exception(static function () {
			(new Constraint\SeekerRule())->apply([
				'contact' => [
					'facebook' => null,
					'instagram' => null,
					'phone_number' => null,
				],
			]);
		}, \UnexpectedValueException::class, 'At least one contact must be specified.');
	}
}

(new SeekerRuleTest())->run();
