<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace FindMyFriends\Unit\Request;

use FindMyFriends\Request;
use Klapuch\Application;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StructuredJsonRequest extends \Tester\TestCase {
	/**
	 * @throws \UnexpectedValueException The property bar is required
	 */
	public function testThrowingOnMissingProperty() {
		(new Request\StructuredJsonRequest(
			new Application\FakeRequest(new Output\Json(['foo' => 'ok']), []),
			new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingField.json')
		))->body();
	}

	/**
	 * @throws \UnexpectedValueException The property age is required (general.age)
	 */
	public function testThrowingOnMissingNestedProperty() {
		(new Request\StructuredJsonRequest(
			new Application\FakeRequest(new Output\Json(['general' => ['firstname' => 'Foo']]), []),
			new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingNestedField.json')
		))->body();
	}

	public function testAddingDefaultValues() {
		Assert::same(
			['foo' => 'ok', 'bar' => null],
			json_decode(
				(new Request\StructuredJsonRequest(
					new Application\FakeRequest(new Output\Json(['foo' => 'ok']), []),
					new \SplFileInfo(__DIR__ . '/../../fixtures/jsonSchema/missingFieldWithDefault.json')
				))->body()->serialization(),
				true
			)
		);
	}
}

(new StructuredJsonRequest())->run();