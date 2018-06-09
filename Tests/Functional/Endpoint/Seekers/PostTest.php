<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Seekers;

use FindMyFriends\Endpoint;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		$response = (new Endpoint\Seekers\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/seeker/post.json')
				)
			),
			$this->database,
			$this->rabbitMq,
			new Encryption\FakeCipher()
		))->response([]);
		$seeker = json_decode($response->body()->serialization(), true);
		Assert::null($seeker);
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput() {
		$response = (new Endpoint\Seekers\Post(
			new Application\FakeRequest(new Output\FakeFormat('{"foo": "bar"}')),
			$this->database,
			$this->rabbitMq,
			new Encryption\FakeCipher()
		))->response([]);
		$seeker = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property email is required'], $seeker);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}

	public function test409ForDuplication() {
		$post = new Endpoint\Seekers\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/seeker/post.json')
				)
			),
			$this->database,
			$this->rabbitMq,
			new Encryption\FakeCipher()
		);
		$post->response([]);
		$response = $post->response([]);
		$seeker = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Email "me@example.com" already exists'], $seeker);
		Assert::same(HTTP_CONFLICT, $response->status());
	}
}

(new PostTest())->run();
