<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ApiEntranceTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testEnteringWithValidBearerToken() {
		session_set_save_handler(
			new class implements \SessionHandlerInterface {
				public function close() {
					return true;
				}

				public function destroy($id) {
					return true;
				}

				public function gc($maxLifeTime) {
					return true;
				}

				public function open($path, $name) {
					return true;
				}

				public function read($id) {
					return igbinary_serialize(['id' => '123']);
				}

				public function write($id, $data) {
					return true;
				}
			},
			true
		);
		Assert::equal(
			new Access\RegisteredSeeker('123', $this->connection),
			(new Access\ApiEntrance(
				$this->connection
			))->enter(['authorization' => sprintf('Bearer 0c3da2dd2900adb00f8f231e4484c1b5')])
		);
	}

	public function testCaseInsensitiveHeaders() {
		session_set_save_handler(
			new class implements \SessionHandlerInterface {
				public function close() {
					return true;
				}

				public function destroy($id) {
					return true;
				}

				public function gc($maxLifeTime) {
					return true;
				}

				public function open($path, $name) {
					return true;
				}

				public function read($id) {
					return igbinary_serialize(['id' => '123']);
				}

				public function write($id, $data) {
					return true;
				}
			},
			true
		);
		Assert::equal(
			new Access\RegisteredSeeker('123', $this->connection),
			(new Access\ApiEntrance(
				$this->connection
			))->enter(['Authorization' => sprintf('Bearer 0c3da2dd2900adb00f8f231e4484c1b5')])
		);
	}

	public function testNoAuthorizationHeaderLeadingToBeGuest() {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->enter([])
		);
	}

	public function testMissingBearerPartLeadingToBeGuest() {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->enter(['authorization' => 'abc'])
		);
	}

	public function testUnknownTokenLeadingToBeGuest() {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->enter(['authorization' => 'Bearer abcdef'])
		);
	}

	public function testExitBecomingGuest() {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->exit()
		);
	}
}

(new ApiEntranceTest())->run();
