<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Demand\SoulmateRequests;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		$requests = json_decode(
			(new V1\Demand\SoulmateRequests\Get(
				new Uri\FakeUri('/', 'v1/demands/1/soulmate_request', []),
				$this->database
			))->response(['demand_id' => $demand, 'page' => 1, 'per_page' => 10, 'sort' => ''])->body()->serialization()
		);
		Assert::count(1, $requests);
		(new Misc\SchemaAssertion(
			$requests,
			new \SplFileInfo(V1\Demand\SoulmateRequests\Get::SCHEMA)
		))->assert();
	}
}

(new GetTest())->run();
