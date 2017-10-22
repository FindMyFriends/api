<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1\Demands;

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
		(new Misc\SampleDemand($this->database))->try();
		$demands = json_decode(
			(new V1\Demands\Get(
				new Uri\FakeUri('/', 'v1/demands', []),
				$this->database,
				new Access\FakeUser(null, ['role' => 'guest'])
			))->template(['page' => 1, 'per_page' => 10, 'sort' => ''])->render()
		);
		Assert::count(2, $demands);
		Assert::notSame([], $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Demand/schema/get.json')
		))->assert();
	}
}

(new Get())->run();