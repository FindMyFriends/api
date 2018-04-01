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
		$c = new Search\SubsequentRequests($this->database);
		$pending = $c->refresh($demand, 'pending');
		$success = $c->refresh($demand, 'succeed', $pending);
		Assert::same($pending, $success);
		$requests = (new TypedQuery(
			$this->database,
			'SELECT demand_id, self_id FROM soulmate_requests'
		))->rows();
		Assert::count(2, $requests);
		Assert::same(
			['demand_id' => $demand, 'self_id' => null],
			$requests[0]
		);
		Assert::same(
			['demand_id' => $demand, 'self_id' => $success],
			$requests[1]
		);
	}
}

(new SubsequentRequestsTest())->run();