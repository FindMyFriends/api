<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demand\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class PostTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Demand\Spots\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../../fixtures/samples/spot/post.json')
				)
			),
			new Uri\FakeUri('https://localhost', 'demands/k5/spots', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response(['id' => $demand]);
		$spot = json_decode($response->body()->serialization(), true);
		Assert::null($spot);
		Assert::same(HTTP_CREATED, $response->status());
		Assert::same('https://localhost/demands/k5/spots', $response->headers()['Location']);
	}

	public function test400OnBadInput(): void {
		Assert::exception(function () {
			(new Endpoint\Demand\Spots\Post(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new Uri\FakeUri('/', 'demands', []),
				$this->connection,
				new Access\FakeSeeker('1', ['role' => 'member'])
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}
}

(new PostTest())->run();
