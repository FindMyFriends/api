<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Interaction;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class CachedDemandsTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testMultipleCallsWithSingleExecution() {
		$origin = $this->mock(Interaction\Demands::class);
		$origin->shouldReceive('count')->once();
		$origin->shouldReceive('all')->once();
		$demands = new Interaction\CachedDemands($origin);
		Assert::equal($demands->count(new Dataset\FakeSelection()), $demands->count(new Dataset\FakeSelection()));
		Assert::equal($demands->all(new Dataset\FakeSelection()), $demands->all(new Dataset\FakeSelection()));
	}
}

(new CachedDemandsTest())->run();
