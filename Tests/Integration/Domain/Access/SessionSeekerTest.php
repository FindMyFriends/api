<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SessionSeekerTest extends TestCase\Runtime {
	public function testCurrentSessionId(): void {
		session_start();
		$_SESSION['id'] = 1;
		Assert::same(
			session_id(),
			(new Access\SessionSeeker(
				new Access\FakeSeeker('1', [])
			))->id()
		);
	}

	public function testValidExpiration(): void {
		$properties = (new Access\SessionSeeker(
			new Access\FakeSeeker('1', [])
		))->properties();
		Assert::same(3600, $properties['expiration']);
	}

	public function testMergingWithSettingPrior(): void {
		$properties = (new Access\SessionSeeker(
			new Access\FakeSeeker('1', ['expiration' => 10, 'foo' => 'bar'])
		))->properties();
		Assert::same(['expiration' => 3600, 'foo' => 'bar'], $properties);
	}
}

(new SessionSeekerTest())->run();
