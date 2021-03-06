<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Activations;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage\TypedQuery;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PostTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection))->try();
		$response = (new Endpoint\Activations\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['code' => $code]))
			),
			$this->connection
		))->response([]);
		Assert::null(json_decode($response->body()->serialization()));
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test404OnUnknown(): void {
		Assert::exception(function () {
			(new Endpoint\Activations\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['code' => '123']))
				),
				$this->connection
			))->response([]);
		}, \UnexpectedValueException::class, 'The verification code does not exist', HTTP_NOT_FOUND);
	}

	public function test410OnUsed(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		$code = (new TypedQuery(
			$this->connection,
			'UPDATE verification_codes SET used_at = NOW() RETURNING code'
		))->field();
		Assert::exception(function () use ($code) {
			(new Endpoint\Activations\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['code' => $code]))
				),
				$this->connection
			))->response([]);
		}, \UnexpectedValueException::class, 'Verification code was already used', HTTP_GONE);
	}
}

(new PostTest())->run();
