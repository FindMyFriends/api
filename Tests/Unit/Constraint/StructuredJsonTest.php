<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class StructuredJsonTest extends TestCase\Runtime {
	/**
	 * @throws \UnexpectedValueException The property bar is required
	 */
	public function testThrowingOnMissingProperty(): void {
		(new Constraint\StructuredJson(
			new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingField.json')
		))->apply(['foo' => 'ok']);
	}

	/**
	 * @throws \UnexpectedValueException The property birth_year_range is required (general.birth_year_range)
	 */
	public function testThrowingOnMissingNestedProperty(): void {
		(new Constraint\StructuredJson(
			new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingNestedField.json')
		))->apply(['general' => ['firstname' => 'Foo']]);
	}

	public function testNoErrorOnEmptyInput(): void {
		Assert::noError(static function() {
			(new Constraint\StructuredJson(
				new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingFieldWithDefault.json')
			))->satisfied([]);
		});
	}
}

(new StructuredJsonTest())->run();
