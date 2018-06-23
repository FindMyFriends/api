<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Demand;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
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
			(new Endpoint\Demand\Get(
				new Hashids(),
				new Uri\FakeUri('/', 'demands/1', []),
				$this->database,
				new Access\FakeSeeker((string) $seeker)
			))->response(['id' => $id])->body()->serialization()
		);
		Assert::same($seeker, $demand->seeker_id);
		(new Misc\SchemaAssertion(
			$demand,
			new \SplFileInfo(__DIR__ . '/../../../../App/Endpoint/Demand/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned() {
		Assert::exception(function() {
			(new Endpoint\Demand\Get(
				new Hashids(),
				new Uri\FakeUri('/', 'demands/1', []),
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'This is not your demand', HTTP_FORBIDDEN);
	}
}

(new GetTest())->run();
