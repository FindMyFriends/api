<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Mail\Verification;

use FindMyFriends\Mail\Verification;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class MessageTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testValidContent(): void {
		(new Misc\SampleSeeker($this->connection, ['email' => 'me@email.cz']))->try();
		Assert::noError(function () {
			(new Verification\Message(
				'me@email.cz',
				$this->connection
			))->content();
			(new Verification\Message(
				'me@email.cz',
				$this->connection
			))->headers();
		});
	}
}

(new MessageTest())->run();
