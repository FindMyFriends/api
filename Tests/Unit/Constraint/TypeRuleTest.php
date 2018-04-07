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

final class TypeRuleTest extends Tester\TestCase {
	public function testPassingOnAllValuesInEnum() {
		$schema = new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'));
		$value = ['status' => 'success', 'size' => 2];
		Assert::true((new Constraint\TypeRule($schema))->satisfied($value));
		Assert::same($value, (new Constraint\TypeRule($schema))->apply($value));
	}

	public function testFailingOnAnyValueOutOfEnum() {
		$schema = new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'));
		$value = ['size' => 2, 'status' => 'foo'];
		Assert::false((new Constraint\TypeRule($schema))->satisfied($value));
		Assert::exception(function() use ($value, $schema) {
			(new Constraint\TypeRule($schema))->apply($value);
		}, \UnexpectedValueException::class, "'status' must be one of: 'success', 'fail' - 'foo' was given");
	}

	public function testPassingOnOnStatedValues() {
		$schema = new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'));
		$value = ['status' => 'success', 'bar' => 'baz'];
		Assert::true((new Constraint\TypeRule($schema))->satisfied($value));
		Assert::same($value, (new Constraint\TypeRule($schema))->apply($value));
	}

	private function testingSchema(): string {
		return json_encode(
			[
				'$schema' => 'http://json-schema.org/draft-04/schema#',
				'additionalProperties' => false,
				'properties' => [
					'status' => [
						'type' => ['string'],
						'enum' => ['success', 'fail'],
					],
					'size' => [
						'type' => ['integer'],
						'enum' => [1, 2, 3],
					],
				],
				'type' => 'object',
			]
		);
	}
}

(new TypeRuleTest())->run();