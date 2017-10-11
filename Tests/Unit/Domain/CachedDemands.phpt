<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Domain;

use FindMyFriends\Domain;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedDemands extends \Tester\TestCase {
	use TestCase\Mockery;

	public function testMultipleCallsWithSingleExecution() {
		$origin = $this->mock(Domain\Demands::class);
		$origin->shouldReceive('count')->once();
		$origin->shouldReceive('all')->once();
		$demands = new Domain\CachedDemands($origin);
		Assert::equal($demands->count(new Dataset\FakeSelection()), $demands->count(new Dataset\FakeSelection()));
		Assert::equal($demands->all(new Dataset\FakeSelection()), $demands->all(new Dataset\FakeSelection()));
	}
}

(new CachedDemands())->run();