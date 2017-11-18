<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

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

	public function testAddingDefaultValues() {
		Assert::same(
			['foo' => 'ok', 'bar' => null],
			(new Constraint\StructuredJson(
				new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingFieldWithDefault.json')
			))->apply(['foo' => 'ok'])
		);
	}

	public function testSatisfactionWithoutPassingDefaultValues() {
		$subject = ['foo' => 'ok'];
		Assert::true(
			(new Constraint\StructuredJson(
				new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingFieldWithDefault.json')
			))->satisfied($subject)
		);
		Assert::same(['foo' => 'ok'], $subject);
	}

	public function testNoErrorOnEmptyInput() {
		Assert::noError(function() {
			(new Constraint\StructuredJson(
				new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingFieldWithDefault.json')
			))->satisfied([]);
		});
	}
}

(new StructuredJsonTest())->run();