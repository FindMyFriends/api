<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Demand\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class DeleteTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->database, 'spot'))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_spot', ['demand_id' => $demand, 'spot_id' => $spot]))->try();
		$response = (new Endpoint\Demand\Spots\Delete(
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $spot]);
		Assert::same('', $response->body()->serialization());
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test403ForNotOwned() {
		Assert::exception(function () {
			(new Endpoint\Demand\Spots\Delete(
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Spot does not belong to you.', HTTP_FORBIDDEN);
	}
}

(new DeleteTest())->run();
