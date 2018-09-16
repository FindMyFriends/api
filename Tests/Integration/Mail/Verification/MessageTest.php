<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Mail\Verification;

use FindMyFriends\Mail\Verification;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class MessageTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testValidContent() {
		(new Misc\SampleSeeker($this->database, ['email' => 'me@email.cz']))->try();
		Assert::noError(function () {
			(new Verification\Message(
				'me@email.cz',
				$this->database
			))->content();
			(new Verification\Message(
				'me@email.cz',
				$this->database
			))->headers();
		});
	}
}

(new MessageTest())->run();
