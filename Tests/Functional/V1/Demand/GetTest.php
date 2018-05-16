<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demand;

use FindMyFriends\Domain\Access;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Hashids\Hashids;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleDemand($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$demand = json_decode(
			(new V1\Demand\Get(
				new Hashids(),
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeSeeker((string) $seeker),
				new Http\FakeRole(true)
			))->response(['id' => $id])->body()->serialization()
		);
		Assert::same($seeker, $demand->seeker_id);
		(new Misc\SchemaAssertion(
			$demand,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Demand/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned() {
		$response = (new V1\Demand\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'v1/demands/1', []),
			$this->database,
			new Access\FakeSeeker('1'),
			new Http\FakeRole(true)
		))->response(['id' => 1]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'This is not your demand'], $demand);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new GetTest())->run();
