<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $demand] = (new Misc\SampleDemand($this->connection))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand]))->try();
		$requests = json_decode(
			(new Endpoint\Demand\SoulmateRequests\Get(
				new Uri\FakeUri('/', 'demands/1/soulmate_request', []),
				$this->connection
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
