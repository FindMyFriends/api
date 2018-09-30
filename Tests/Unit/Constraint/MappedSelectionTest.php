<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class MappedSelectionTest extends TestCase\Runtime {
	public function testMappingAllTypes(): void {
		Assert::same(
			['sort' => ['general_age' => 'ASC'], 'filter' => ['general_name' => 'Dom']],
			(new Constraint\MappedSelection(
				new Dataset\FakeSelection(
					[
						'sort' => ['general.age' => 'ASC'],
						'filter' => ['general.name' => 'Dom'],
					]
				)
			))->criteria()
		);
	}
}

(new MappedSelectionTest())->run();
