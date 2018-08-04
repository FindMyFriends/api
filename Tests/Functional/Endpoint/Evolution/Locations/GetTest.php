<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Evolution\Locations;

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
		['id' => $change] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $location] = (new Misc\SamplePostgresData($this->database, 'evolution_location', ['evolution_id' => $change]))->try();
		$response = (new Endpoint\Evolution\Spots\Get(
			new Hashids('a'),
			new Hashids('b'),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $change]);
		$locations = json_decode($response->body()->serialization());
		Assert::count(1, $locations);
		$locationId = (new TypedQuery(
			$this->database,
			'SELECT location_id FROM evolution_locations WHERE id = ?',
			[$location]
		))->field();
		Assert::same((new Hashids('a'))->encode($locationId), $locations[0]->id);
		Assert::same((new Hashids('b'))->encode($change), $locations[0]->evolution_id);
		(new Misc\SchemaAssertion(
			$locations,
			new \SplFileInfo(__DIR__ . '/../../../../../App/Endpoint/Evolution/Locations/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned() {
		Assert::exception(function () {
			(new Endpoint\Evolution\Spots\Get(
				new Hashids('a'),
				new Hashids('b'),
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.', HTTP_FORBIDDEN);
	}
}

(new GetTest())->run();
