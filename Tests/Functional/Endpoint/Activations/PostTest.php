<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Activations;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage\TypedQuery;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->database))->try();
		$response = (new Endpoint\Activations\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['code' => $code]))
			),
			$this->database
		))->response([]);
		Assert::null(json_decode($response->body()->serialization()));
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test404OnUnknown() {
		Assert::exception(function () {
			(new Endpoint\Activations\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['code' => '123']))
				),
				$this->database
			))->response([]);
		}, \UnexpectedValueException::class, 'The verification code does not exist', HTTP_NOT_FOUND);
	}

	public function test410OnUsed() {
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		$code = (new TypedQuery(
			$this->database,
			'UPDATE verification_codes SET used_at = NOW() RETURNING code'
		))->field();
		Assert::exception(function () use ($code) {
			(new Endpoint\Activations\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['code' => $code]))
				),
				$this->database
			))->response([]);
		}, \UnexpectedValueException::class, 'Verification code was already used', HTTP_GONE);
	}
}

(new PostTest())->run();
