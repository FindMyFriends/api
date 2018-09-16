<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class RegisteredSeekerTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testInfoAboutRegisteredSeeker() {
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz', 'role' => 'member']))->try();
		$seeker = new Access\RegisteredSeeker((string) $id, $this->database);
		Assert::same((string) $id, $seeker->id());
		Assert::same(
			['email' => 'foo@bar.cz', 'role' => 'member'],
			$seeker->properties()
		);
	}

	public function testThrowingOnNotRegisteredSeeker() {
		$seeker = new Access\RegisteredSeeker('1', $this->database);
		Assert::exception(static function() use ($seeker) {
			$seeker->id();
		}, \UnexpectedValueException::class, 'The seeker has not been registered yet');
		Assert::exception(static function() use ($seeker) {
			$seeker->properties();
		}, \UnexpectedValueException::class, 'The seeker has not been registered yet');
	}
}

(new RegisteredSeekerTest())->run();
