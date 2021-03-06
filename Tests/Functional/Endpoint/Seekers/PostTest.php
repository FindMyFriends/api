<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Seekers;

use FindMyFriends\Endpoint;
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
		$response = (new Endpoint\Seekers\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/seeker/post.json')
				)
			),
			$this->connection,
			$this->rabbitMq,
			new Encryption\FakeCipher()
		))->response([]);
		$seeker = json_decode($response->body()->serialization(), true);
		Assert::null($seeker);
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput(): void {
		Assert::exception(function() {
			(new Endpoint\Seekers\Post(
				new Application\FakeRequest(new Output\FakeFormat('{"foo": "bar"}')),
				$this->connection,
				$this->rabbitMq,
				new Encryption\FakeCipher()
			))->response([]);
		}, \UnexpectedValueException::class, 'The property general is required');
	}

	public function test409ForDuplication(): void {
		$post = new Endpoint\Seekers\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/seeker/post.json')
				)
			),
			$this->connection,
			$this->rabbitMq,
			new Encryption\FakeCipher()
		);
		$post->response([]);
		Assert::exception(static function() use ($post) {
			$post->response([]);
		}, \UnexpectedValueException::class, 'Email me@example.com already exists', HTTP_CONFLICT);
	}
}

(new PostTest())->run();
