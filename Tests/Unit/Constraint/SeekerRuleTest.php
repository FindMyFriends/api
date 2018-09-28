<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class SeekerRuleTest extends Tester\TestCase {
	public function testAtLeastOneNotNullContact() {
		$subject = [
			'contact' => [
				'facebook' => 'klapuchdominik',
				'instagram' => null,
				'phone_number' => null,
			],
		];
		Assert::same($subject, (new Constraint\SeekerRule())->apply($subject));
	}

	public function testThrowingOnAllNull() {
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
