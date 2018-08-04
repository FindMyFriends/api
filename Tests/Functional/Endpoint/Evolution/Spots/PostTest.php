<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Evolution\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Evolution\Spots\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../../fixtures/samples/spot/post.json')
				)
			),
			new Uri\FakeUri('https://localhost', 'evolutions/k5/spots', []),
			$this->database,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response(['id' => $change]);
		$spot = json_decode($response->body()->serialization(), true);
		Assert::null($spot);
		Assert::same(HTTP_CREATED, $response->status());
		Assert::same('https://localhost/evolutions/k5/spots', $response->headers()['Spot']);
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\Evolution\Spots\Post(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new Uri\FakeUri('/', 'evolutions', []),
				$this->database,
				new Access\FakeSeeker('1', ['role' => 'member'])
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}
}

(new PostTest())->run();
