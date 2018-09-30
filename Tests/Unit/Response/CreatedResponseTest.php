<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CreatedResponseTest extends TestCase\Runtime {
	public function testPrependLocationHeader(): void {
		Assert::same(
			['Location' => 'http://localhost', 'Accept' => 'text/html'],
			(new Response\CreatedResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['Accept' => 'text/html']),
				new Uri\FakeUri('http://localhost', '/books/1')
			))->headers()
		);
	}

	public function test201CreatedStatusCode(): void {
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
