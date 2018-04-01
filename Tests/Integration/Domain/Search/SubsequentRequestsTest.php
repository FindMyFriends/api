<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Storage\TypedQuery;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

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
}

(new SubsequentRequestsTest())->run();