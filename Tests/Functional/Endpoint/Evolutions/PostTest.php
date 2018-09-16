<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Evolutions;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Evolutions\Post(
			new Hashids(),
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/post.json')
				)
			),
			new FakeUri('https://localhost', 'evolutions', []),
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response([]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_CREATED, $response->status());
		Assert::same('https://localhost/evolutions/k5', $response->headers()['Location']);
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\Evolutions\Post(
				new Hashids(),
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new FakeUri('/', 'evolutions', []),
				$this->database,
				$this->elasticsearch,
				new Access\FakeSeeker('1', ['role' => 'member'])
			))->response([]);
		}, \UnexpectedValueException::class, 'The property general is required');
	}
}

(new PostTest())->run();
