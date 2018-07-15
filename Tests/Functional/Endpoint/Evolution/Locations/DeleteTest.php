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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class DeleteTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $location] = (new Misc\SamplePostgresData($this->database, 'location'))->try();
		(new Misc\SamplePostgresData($this->database, 'evolution_location', ['evolution_id' => $change, 'location_id' => $location]))->try();
		$response = (new Endpoint\Evolution\Locations\Delete(
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $location]);
		Assert::same('', $response->body()->serialization());
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test403ForNotOwned() {
		Assert::exception(function () {
			(new Endpoint\Evolution\Locations\Delete(
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Location does not belong to you', HTTP_FORBIDDEN);
	}
}

(new DeleteTest())->run();
