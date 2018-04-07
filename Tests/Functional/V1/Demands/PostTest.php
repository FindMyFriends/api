<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demands;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Hashids\Hashids;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		$response = (new V1\Demands\Post(
			new Hashids(),
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/post.json')
				)
			),
			new FakeUri('/', 'v1/demands', []),
			$this->database,
			$this->rabbitMq,
			new Access\FakeUser((string) $seeker, ['role' => 'guest'])
		))->response([]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput() {
		$response = (new V1\Demands\Post(
			new Hashids(),
			new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
			new FakeUri('/', 'v1/demands', []),
			$this->database,
			$this->rabbitMq,
			new Access\FakeUser('1', ['role' => 'guest'])
		))->response([]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property location is required'], $demand);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}
}

(new PostTest())->run();