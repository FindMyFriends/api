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
final class ContactRuleTest extends Tester\TestCase {
	public function testAtLeastOneNotNullContact() {
		$contact = [
			'facebook' => 'klapuchdominik',
			'instagram' => null,
			'phone_number' => null,
		];
		Assert::same($contact, (new Constraint\ContactRule())->apply($contact));
	}

	public function testThrowingOnAllNull() {
		Assert::exception(static function () {
			(new Constraint\ContactRule())->apply([
				'facebook' => null,
				'instagram' => null,
				'phone_number' => null,
			]);
		}, \UnexpectedValueException::class, 'At least one contact must be specified.');
	}
}

(new ContactRuleTest())->run();
