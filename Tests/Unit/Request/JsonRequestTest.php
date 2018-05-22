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
	public function testConversionToJson() {
		Assert::equal(
			new Output\Json(['foo' => 'bar']),
			(new Request\JsonRequest(
				new Application\FakeRequest(new Output\FakeFormat('{"foo":"bar"}'))
			))->body()
		);
	}
}

(new JsonRequestTest())->run();
