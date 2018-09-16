<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Access;

use FindMyFriends\Domain\Access;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class ConstantSeekerTest extends Tester\TestCase {
	public function testPropertiesWithoutSensitiveData() {
		$seeker = new Access\ConstantSeeker(
			'1',
			['id' => 1, 'email' => '@', 'role' => ['master'], 'password' => 'secret']
		);
		Assert::same(['email' => '@', 'role' => ['master']], $seeker->properties());
	}

	public function testCaseInsensitivePropertiesWithoutSensitiveData() {
		$seeker = new Access\ConstantSeeker(
			'1',
			['Id' => 1, 'EmaiL' => '@', 'RolE' => ['master'], 'PaSSworD' => 'secret']
		);
		Assert::same(['EmaiL' => '@', 'RolE' => ['master']], $seeker->properties());
	}

	public function testAllSensitiveDataEndingWithEmptyProperties() {
		$seeker = new Access\ConstantSeeker(
			'1',
			['id' => 1, 'password' => 'secret']
		);
		Assert::same([], $seeker->properties());
	}
}

(new ConstantSeekerTest())->run();
