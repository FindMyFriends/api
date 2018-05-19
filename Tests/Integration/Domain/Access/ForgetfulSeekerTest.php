<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ForgetfulSeekerTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testSeekerWithKnownReminder() {
		$reminder = str_repeat('x', 141);
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['seeker_id' => $id, 'reminder' => $reminder]))->try();
		$seeker = new Access\ForgetfulSeeker($reminder, $this->database);
		Assert::same((string) $id, $seeker->id());
		Assert::same(
			['email' => 'foo@bar.cz', 'role' => 'member'],
			$seeker->properties()
		);
	}

	public function testNoSeekerOnInvalidReminder() {
		$seeker = new Access\ForgetfulSeeker('invalid:reminder', $this->database);
		Assert::same('0', $seeker->id());
		Assert::same([], $seeker->properties());
	}
}

(new ForgetfulSeekerTest())->run();
