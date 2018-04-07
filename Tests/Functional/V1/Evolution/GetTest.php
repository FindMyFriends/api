<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Evolution;

use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Hashids\Hashids;
use Klapuch\Access;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new V1\Evolution\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'v1/evolutions/1', []),
			$this->database,
			new Access\FakeUser((string) $seeker),
			new Http\FakeRole(true)
		))->response(['id' => $id]);
		$evolution = json_decode($response->body()->serialization());
		Assert::same((new Hashids())->encode($id), $evolution->id);
		(new Misc\SchemaAssertion(
			$evolution,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Evolution/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned() {
		$response = (new V1\Evolution\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'v1/evolutions/1', []),
			$this->database,
			new Access\FakeUser('1'),
			new Http\FakeRole(true)
		))->response(['id' => 1]);
		$evolution = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'You are not permitted to see this evolution change.'], $evolution);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new GetTest())->run();