<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1\Demand;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class DeleteTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleDemand($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new V1\Demand\Delete(
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test404OnNotExisting() {
		$response = (new V1\Demand\Delete(
			$this->database,
			new Access\FakeSeeker()
		))->response(['id' => 1]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Demand does not exist'], $demand);
		Assert::same(HTTP_NOT_FOUND, $response->status());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$response = (new V1\Demand\Delete(
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'This is not your demand'], $demand);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new DeleteTest())->run();
