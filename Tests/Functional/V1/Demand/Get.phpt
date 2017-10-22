<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1\Demand;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleDemand($this->database))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => 10]))->try();
		$demand = json_decode(
			(new V1\Demand\Get(
				new Uri\FakeUri('/', sprintf('v1/demands/%d', $id), []),
				$this->database,
				new Access\FakeUser(null, ['role' => 'guest']),
				$this->redis
			))->template(['id' => $id])->render()
		);
		Assert::same(10, $demand->seeker_id);
		(new Misc\SchemaAssertion(
			$demand,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Demand/schema/get.json')
		))->assert();
	}
}

(new Get())->run();