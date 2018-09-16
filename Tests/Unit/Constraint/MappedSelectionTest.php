<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Klapuch\Dataset;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class MappedSelectionTest extends Tester\TestCase {
	public function testMappingAllTypes() {
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
