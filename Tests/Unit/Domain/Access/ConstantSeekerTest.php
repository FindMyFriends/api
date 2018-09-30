<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ConstantSeekerTest extends TestCase\Runtime {
	public function testPropertiesWithoutSensitiveData(): void {
		$seeker = new Access\ConstantSeeker(
			'1',
			['id' => 1, 'email' => '@', 'role' => ['master'], 'password' => 'secret']
		);
		Assert::same(['email' => '@', 'role' => ['master']], $seeker->properties());
	}

	public function testCaseInsensitivePropertiesWithoutSensitiveData(): void {
		$seeker = new Access\ConstantSeeker(
			'1',
			['Id' => 1, 'EmaiL' => '@', 'RolE' => ['master'], 'PaSSworD' => 'secret']
		);
		Assert::same(['EmaiL' => '@', 'RolE' => ['master']], $seeker->properties());
	}

	public function testAllSensitiveDataEndingWithEmptyProperties(): void {
		$seeker = new Access\ConstantSeeker(
			'1',
			['id' => 1, 'password' => 'secret']
		);
		Assert::same([], $seeker->properties());
	}
}

(new ConstantSeekerTest())->run();
