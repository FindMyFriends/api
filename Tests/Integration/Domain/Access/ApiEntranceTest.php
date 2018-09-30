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
final class ApiEntranceTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testEnteringWithValidBearerToken(): void {
		session_set_save_handler(
			new class implements \SessionHandlerInterface {
				public function close(): bool {
					return true;
				}

				/**
				 * @param string $id
				 * @return bool
				 */
				public function destroy($id): bool {
					return true;
				}

				/**
				 * @param int $maxLifeTime
				 * @return bool
				 */
				public function gc($maxLifeTime): bool {
					return true;
				}

				/**
				 * @param string $path
				 * @param string $name
				 * @return bool
				 */
				public function open($path, $name): bool {
					return true;
				}

				/**
				 * @param string $id
				 * @return string|bool
				 */
				public function read($id) {
					return igbinary_serialize(['id' => '123']);
				}

				/**
				 * @param string $id
				 * @param string $data
				 * @return bool
				 */
				public function write($id, $data): bool {
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

	public function testCaseInsensitiveHeaders(): void {
		session_set_save_handler(
			new class implements \SessionHandlerInterface {
				public function close(): bool {
					return true;
				}

				/**
				 * @param string $id
				 * @return bool
				 */
				public function destroy($id): bool {
					return true;
				}

				/**
				 * @param int $maxLifeTime
				 * @return bool
				 */
				public function gc($maxLifeTime): bool {
					return true;
				}

				/**
				 * @param string $path
				 * @param string $name
				 * @return bool
				 */
				public function open($path, $name): bool {
					return true;
				}

				/**
				 * @param string $id
				 * @return string|bool
				 */
				public function read($id) {
					return igbinary_serialize(['id' => '123']);
				}

				/**
				 * @param string $id
				 * @param string $data
				 * @return bool
				 */
				public function write($id, $data): bool {
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

	public function testNoAuthorizationHeaderLeadingToBeGuest(): void {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->enter([])
		);
	}

	public function testMissingBearerPartLeadingToBeGuest(): void {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->enter(['authorization' => 'abc'])
		);
	}

	public function testUnknownTokenLeadingToBeGuest(): void {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->enter(['authorization' => 'Bearer abcdef'])
		);
	}

	public function testExitBecomingGuest(): void {
		Assert::equal(
			new Access\Guest(),
			(new Access\ApiEntrance($this->connection))->exit()
		);
	}
}

(new ApiEntranceTest())->run();
