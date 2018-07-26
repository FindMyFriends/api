<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Request;

use FindMyFriends\Request;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class JsonRequestTest extends Tester\TestCase {
	public function testThrowingOnInvalidJson() {
		Assert::exception(function () {
			(new Request\JsonRequest(
				new Application\FakeRequest(new Output\FakeFormat(''))
			))->body()->serialization();
		}, \UnexpectedValueException::class, 'JSON is not valid', JSON_ERROR_SYNTAX);
	}

	public function testPassingOnValidJson() {
		Assert::same(
			json_encode(['x' => 1], JSON_PRETTY_PRINT),
			(new Request\JsonRequest(
				new Application\FakeRequest(new Output\FakeFormat('{"x":1}'))
			))->body()->serialization()
		);
	}
}

(new JsonRequestTest())->run();
