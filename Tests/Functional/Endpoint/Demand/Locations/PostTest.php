<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Demand\Locations;

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
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Demand\Spots\Post(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../../fixtures/samples/location/post.json')
				)
			),
			new Uri\FakeUri('https://localhost', 'demands/k5/locations', []),
			$this->database,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response(['id' => $demand]);
		$location = json_decode($response->body()->serialization(), true);
		Assert::null($location);
		Assert::same(HTTP_CREATED, $response->status());
		Assert::same('https://localhost/demands/k5/locations', $response->headers()['Spot']);
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\Demand\Spots\Post(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new Uri\FakeUri('/', 'demands', []),
				$this->database,
				new Access\FakeSeeker('1', ['role' => 'member'])
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}
}

(new PostTest())->run();
