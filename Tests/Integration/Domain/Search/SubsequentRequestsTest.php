<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Storage\TypedQuery;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class SubsequentRequestsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testSubsequentId() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		$requests = new Search\SubsequentRequests($demand, $this->database);
		$pending = $requests->refresh('pending');
		$success = $requests->refresh('succeed', $pending);
		Assert::same($pending, $success);
		$rows = (new TypedQuery(
			$this->database,
			'SELECT demand_id, self_id FROM soulmate_requests'
		))->rows();
		Assert::count(2, $rows);
		Assert::same(
			['demand_id' => $demand, 'self_id' => null],
			$rows[0]
		);
		Assert::same(
			['demand_id' => $demand, 'self_id' => $success],
			$rows[1]
		);
	}

	public function testAllFromDemand() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		['id' => $soulmateRequestId] = (new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand, 'self_id' => $soulmateRequestId]))->try();
		$request = new Search\SubsequentRequests($demand, $this->database);
		Assert::same(2, $request->count(new Dataset\EmptySelection()));
		Assert::same(2, iterator_count($request->all(new Dataset\EmptySelection())));
	}

	public function testThrowingOnRequestInProgress() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		$request = new Search\SubsequentRequests($demand, $this->database);
		$request->refresh('processing');
		Assert::exception(static function () use ($request) {
			$request->refresh('processing');
		}, \UnexpectedValueException::class, 'Seeking for soulmate is already in progress');
	}
}

(new SubsequentRequestsTest())->run();
