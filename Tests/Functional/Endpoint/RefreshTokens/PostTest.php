<?php
declare(strict_types = 1);

/**
 * @phpIni session.save_handler=files
 * @phpIni session.save_path=
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\RefreshTokens;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleSeeker($this->database, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		session_start();
		$_SESSION['id'] = '1';
		$token = session_id();
		session_write_close();
		$response = (new Endpoint\RefreshTokens\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['token' => $token]))
			),
			$this->database
		))->response([]);
		$access = json_decode($response->body()->serialization(), true);
		Assert::true(isset($access['token']));
		Assert::true(isset($access['expiration']));
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\RefreshTokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['foo' => 'bar']))
				),
				$this->database
			))->response([]);
		}, \UnexpectedValueException::class, 'The property token is required');
	}

	public function test403OnUnknownToken() {
		Assert::exception(function () {
			(new Endpoint\RefreshTokens\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['token' => 'abc']))
				),
				$this->database
			))->response([]);
		}, \UnexpectedValueException::class, 'Provided token is not valid.', HTTP_FORBIDDEN);
	}
}

(new PostTest())->run();