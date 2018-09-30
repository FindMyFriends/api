<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demand\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Storage\TypedQuery;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->connection, 'demand_spot', ['demand_id' => $demand]))->try();
		$response = (new Endpoint\Demand\Spots\Get(
			new Hashids('a'),
			new Hashids('b'),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $demand]);
		$spots = json_decode($response->body()->serialization());
		Assert::count(1, $spots);
		$spotId = (new TypedQuery(
			$this->connection,
			'SELECT spot_id FROM demand_spots WHERE id = ?',
			[$spot]
		))->field();
		Assert::same((new Hashids('a'))->encode($spotId), $spots[0]->id);
		Assert::same((new Hashids('b'))->encode($demand), $spots[0]->demand_id);
		(new Misc\SchemaAssertion(
			$spots,
			new \SplFileInfo(__DIR__ . '/../../../../../App/Endpoint/Demand/Spots/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned(): void {
		Assert::exception(function () {
			(new Endpoint\Demand\Spots\Get(
				new Hashids('a'),
				new Hashids('b'),
				$this->connection,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Demand does not belong to you.', HTTP_FORBIDDEN);
	}
}

(new GetTest())->run();
