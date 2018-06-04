<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Tokens;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleSeeker($this->database, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		$response = (new V1\Tokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					json_encode(['email' => 'foo@bar.cz', 'password' => '123'])
				)
			),
			$this->database,
			new Encryption\FakeCipher(true)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::true(isset($access['token']));
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput() {
		$response = (new V1\Tokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					json_encode(['foo' => 'bar'])
				)
			),
			$this->database,
			new Encryption\FakeCipher(true)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property email is required'], $access);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}

	public function test403OnUnknownEmail() {
		$response = (new V1\Tokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					json_encode(['email' => 'foo@baz.cz', 'password' => '123'])
				)
			),
			$this->database,
			new Encryption\FakeCipher(false)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Email "foo@baz.cz" does not exist'], $access);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}

	public function test403OnWrongPassword() {
		(new Misc\SampleSeeker($this->database, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		$response = (new V1\Tokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					json_encode(['email' => 'foo@bar.cz', 'password' => '123'])
				)
			),
			$this->database,
			new Encryption\FakeCipher(false)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Wrong password'], $access);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}

	public function test403OnNotVerifiedCode() {
		(new Misc\SampleSeeker($this->database, ['email' => 'foo@bar.cz']))->try();
		$response = (new V1\Tokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					json_encode(['email' => 'foo@bar.cz', 'password' => '123'])
				)
			),
			$this->database,
			new Encryption\FakeCipher(true)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Email has not been verified yet'], $access);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new PostTest())->run();