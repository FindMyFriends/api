<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Evolution\Spots;

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
		['id' => $spot] = (new Misc\SamplePostgresData($this->database, 'evolution_spot', ['evolution_id' => $change]))->try();
		$response = (new Endpoint\Evolution\Spots\Get(
			new Hashids('a'),
			new Hashids('b'),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $change]);
		$spots = json_decode($response->body()->serialization());
		Assert::count(1, $spots);
		$spotId = (new TypedQuery(
			$this->database,
			'SELECT spot_id FROM evolution_spots WHERE id = ?',
			[$spot]
		))->field();
		Assert::same((new Hashids('a'))->encode($spotId), $spots[0]->id);
		Assert::same((new Hashids('b'))->encode($change), $spots[0]->evolution_id);
		(new Misc\SchemaAssertion(
			$spots,
			new \SplFileInfo(__DIR__ . '/../../../../../App/Endpoint/Evolution/Spots/schema/get.json')
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