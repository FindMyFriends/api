<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class StructuredJsonTest extends Tester\TestCase {
	/**
	 * @throws \UnexpectedValueException The property bar is required
	 */
	public function testThrowingOnMissingProperty() {
		(new Constraint\StructuredJson(
			new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingField.json')
		))->apply(['foo' => 'ok']);
	}

	/**
	 * @throws \UnexpectedValueException The property birth_year is required (general.birth_year)
	 */
	public function testThrowingOnMissingNestedProperty() {
		(new Constraint\StructuredJson(
			new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingNestedField.json')
		))->apply(['general' => ['firstname' => 'Foo']]);
	}

	public function testNoErrorOnEmptyInput() {
		Assert::noError(static function() {
			(new Constraint\StructuredJson(
				new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingFieldWithDefault.json')
			))->satisfied([]);
		});
	}
}

(new StructuredJsonTest())->run();
