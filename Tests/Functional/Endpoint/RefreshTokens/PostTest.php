<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\RefreshTokens;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
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
		session_start();
		$_SESSION['id'] = '1';
		$token = session_id();
		session_write_close();
		$response = (new Endpoint\RefreshTokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['token' => $token]))
			)
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::true(isset($access['token']));
		Assert::true(isset($access['expiration']));
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput(): void {
		Assert::exception(static function () {
			(new Endpoint\RefreshTokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['foo' => 'bar']))
				)
			))->response([]);
		}, \UnexpectedValueException::class, 'The property token is required');
	}

	public function test403OnUnknownToken(): void {
		Assert::exception(static function () {
			(new Endpoint\RefreshTokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['token' => 'abc']))
				)
			))->response([]);
		}, \UnexpectedValueException::class, 'Provided token is not valid.', HTTP_FORBIDDEN);
	}
}

(new PostTest())->run();
