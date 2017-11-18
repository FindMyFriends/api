<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demand;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleDemand($this->database))->try();
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$demand = json_decode(
			(new V1\Demand\Get(
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeUser(null, ['role' => 'guest']),
				$this->redis
			))->template(['id' => $id])->render()
		);
		Assert::same($seeker, $demand->seeker_id);
		(new Misc\SchemaAssertion(
			$demand,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Demand/schema/get.json')
		))->assert();
	}

	public function test404ForNotExisting() {
		$demand = json_decode(
			(new V1\Demand\Get(
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeUser(null, ['role' => 'guest']),
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Demand 1 does not exist'], $demand);
		Assert::same(HTTP_NOT_FOUND, http_response_code());
	}
}

(new GetTest())->run();