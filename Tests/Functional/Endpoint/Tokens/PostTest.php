<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Tokens;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PostTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		(new Misc\SampleSeeker($this->connection, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		$response = (new Endpoint\Tokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					json_encode(['email' => 'foo@bar.cz', 'password' => '123'])
				)
			),
			$this->connection,
			new Encryption\FakeCipher(true)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::true(isset($access['token']));
		Assert::true(isset($access['expiration']));
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput(): void {
		Assert::exception(function () {
			(new Endpoint\Tokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(
						json_encode(['foo' => 'bar'])
					)
				),
				$this->connection,
				new Encryption\FakeCipher(true)
			))->response([]);
		}, \UnexpectedValueException::class, 'The property email is required');
	}

	public function test403OnUnknownEmail(): void {
		Assert::exception(function () {
			(new Endpoint\Tokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(
						json_encode(['email' => 'foo@baz.cz', 'password' => '123'])
					)
				),
				$this->connection,
				new Encryption\FakeCipher(false)
			))->response([]);
		}, \UnexpectedValueException::class, 'Email "foo@baz.cz" does not exist', HTTP_FORBIDDEN);
	}

	public function test403OnWrongPassword(): void {
		(new Misc\SampleSeeker($this->connection, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		Assert::exception(function () {
			(new Endpoint\Tokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(
						json_encode(['email' => 'foo@bar.cz', 'password' => '123'])
					)
				),
				$this->connection,
				new Encryption\FakeCipher(false)
			))->response([]);
		}, \UnexpectedValueException::class, 'Wrong password', HTTP_FORBIDDEN);
	}

	public function test403OnNotVerifiedCode(): void {
		(new Misc\SampleSeeker($this->connection, ['email' => 'foo@bar.cz']))->try();
		Assert::exception(function () {
			(new Endpoint\Tokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(
						json_encode(['email' => 'foo@bar.cz', 'password' => '123'])
					)
				),
				$this->connection,
				new Encryption\FakeCipher(true)
			))->response([]);
		}, \UnexpectedValueException::class, 'Email has not been verified yet', HTTP_FORBIDDEN);
	}
}

(new PostTest())->run();
