<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Evolutions;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Hashids\Hashids;
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
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new V1\Evolutions\Post(
			new Hashids(),
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/post.json')
				)
			),
			new FakeUri('/', 'v1/evolutions', []),
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response([]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_CREATED, $response->status());
	}

	public function test400OnBadInput() {
		$response = (new V1\Evolutions\Post(
			new Hashids(),
			new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
			new FakeUri('/', 'v1/evolutions', []),
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker('1', ['role' => 'member'])
		))->response([]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property general is required'], $demand);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}
}

(new PostTest())->run();
