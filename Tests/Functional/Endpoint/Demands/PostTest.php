<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demands;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri\FakeUri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PostTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		$response = (new Endpoint\Demands\Post(
			new Hashids(),
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/post.json')
				)
			),
			new FakeUri('https://localhost', 'demands', []),
			$this->connection,
			$this->rabbitMq,
			new Access\FakeSeeker((string) $seeker, ['role' => 'guest'])
		))->response([]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_CREATED, $response->status());
		Assert::same('https://localhost/demands/jR', $response->headers()['Location']);
	}

	public function test400OnBadInput(): void {
		Assert::exception(function () {
			(new Endpoint\Demands\Post(
				new Hashids(),
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new FakeUri('/', 'demands', []),
				$this->connection,
				$this->rabbitMq,
				new Access\FakeSeeker('1', ['role' => 'guest'])
			))->response([]);
		}, \UnexpectedValueException::class, 'The property note is required');
	}
}

(new PostTest())->run();
