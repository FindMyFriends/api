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
use Hashids\Hashids;
use Klapuch\Storage\TypedQuery;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $location] = (new Misc\SamplePostgresData($this->database, 'demand_location', ['demand_id' => $demand]))->try();
		$response = (new Endpoint\Demand\Locations\Get(
			new Hashids('a'),
			new Hashids('b'),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $demand]);
		$locations = json_decode($response->body()->serialization());
		Assert::count(1, $locations);
		$locationId = (new TypedQuery(
			$this->database,
			'SELECT location_id FROM demand_locations WHERE id = ?',
			[$location]
		))->field();
		Assert::same((new Hashids('a'))->encode($locationId), $locations[0]->id);
		Assert::same((new Hashids('b'))->encode($demand), $locations[0]->demand_id);
		(new Misc\SchemaAssertion(
			$locations,
			new \SplFileInfo(__DIR__ . '/../../../../../App/Endpoint/Demand/Locations/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned() {
		Assert::exception(function () {
			(new Endpoint\Demand\Locations\Get(
				new Hashids('a'),
				new Hashids('b'),
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Demand does not belong to you.', HTTP_FORBIDDEN);
	}
}

(new GetTest())->run();