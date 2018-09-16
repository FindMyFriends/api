<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		$requests = json_decode(
			(new Endpoint\Demand\SoulmateRequests\Get(
				new Uri\FakeUri('/', 'demands/1/soulmate_request', []),
				$this->database
			))->response(['demand_id' => $demand, 'page' => 1, 'per_page' => 10, 'sort' => ''])->body()->serialization()
		);
		Assert::count(1, $requests);
		(new Misc\SchemaAssertion(
			$requests,
			new \SplFileInfo(Endpoint\Demand\SoulmateRequests\Get::SCHEMA)
		))->assert();
	}
}

(new GetTest())->run();
