<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SessionSeekerTest extends Tester\TestCase {
	public function testCurrentSessionId() {
		session_start();
		$_SESSION['id'] = 1;
		Assert::same(
			session_id(),
			(new Access\SessionSeeker(
				new Access\FakeSeeker('1', [])
			))->id()
		);
	}

	public function testValidExpiration() {
		$properties = (new Access\SessionSeeker(
			new Access\FakeSeeker('1', [])
		))->properties();
		Assert::same(3600, $properties['expiration']);
	}

	public function testMergingWithSettingPrior() {
		$properties = (new Access\SessionSeeker(
			new Access\FakeSeeker('1', ['expiration' => 10, 'foo' => 'bar'])
		))->properties();
		Assert::same(['expiration' => 3600, 'foo' => 'bar'], $properties);
	}
}

(new SessionSeekerTest())->run();
