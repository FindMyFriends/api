<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class LimitedForgottenPasswordsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	/**
	 * @throws \OverflowException You have reached limit 3 forgotten passwords in last 24 hours
	 */
	public function testThrowinOnOversteppedReminding(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@gmail.com']))->try();
		foreach ([
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-1 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-2 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-3 hour'))->format('Y-m-d')],
		] as $row) {
			(new Misc\SamplePostgresData($this->connection, 'forgotten_password', $row))->try();
		}
		(new Access\LimitedForgottenPasswords(
			new Access\FakeForgottenPasswords(),
			$this->connection
		))->remind('foo@gmail.com');
	}

	public function testRemindingInAllowedTimeRange(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@gmail.com']))->try();
		foreach ([
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-25 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-25 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-25 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-25 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-24 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-24 hour'))->format('Y-m-d')],
			['seeker_id' => $seeker, 'used' => false, 'reminded_at' => (new \DateTimeImmutable('-26 hour'))->format('Y-m-d')],
		] as $row) {
			(new Misc\SamplePostgresData($this->connection, 'forgotten_password', $row))->try();
		}
		Assert::noError(
			function() {
				(new Access\LimitedForgottenPasswords(
					new Access\FakeForgottenPasswords(new Access\FakePassword()),
					$this->connection
				))->remind('foo@gmail.com');
			}
		);
	}
}

(new LimitedForgottenPasswordsTest())->run();
