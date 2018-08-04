<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CreatedResponseTest extends Tester\TestCase {
	public function testPrependLocationHeader() {
		Assert::same(
			['Spot' => 'http://localhost', 'Accept' => 'text/html'],
			(new Response\CreatedResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['Accept' => 'text/html']),
				new Uri\FakeUri('http://localhost', '/books/1')
			))->headers()
		);
	}

	public function test201CreatedStatusCode() {
		Assert::same(
			HTTP_CREATED,
			(new Response\CreatedResponse(
				new Application\FakeResponse(),
				new Uri\FakeUri()
			))->status()
		);
	}
}

(new CreatedResponseTest())->run();
