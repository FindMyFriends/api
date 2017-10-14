<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Klapuch\Application;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HttpResponse extends \Tester\TestCase {
	public function testHeadersWithFirstUpperAndRestLower() {
		Assert::same(
			['Accept' => 'text/plain'],
			(new Response\HttpResponse(
				new Application\FakeResponse(null, []),
				['aCCept' => 'text/plain']
			))->headers()
		);
	}

	public function testHeadersAsTwoSeparateWords() {
		Assert::same(
			['Content-Type' => 'text/html'],
			(new Response\HttpResponse(
				new Application\FakeResponse(null, []),
				['content-tYpE' => 'text/html']
			))->headers()
		);
	}

	public function testPassedHeadersWithPrecedence() {
		Assert::same(
			[
				'Content-Type' => 'text/html',
				'Accept' => 'text/xml',
			],
			(new Response\HttpResponse(
				new Application\FakeResponse(
					null,
					[
						'Content-Type' => 'text/plain',
						'Accept' => 'text/xml',
					]
				),
				['Content-Type' => 'text/html']
			))->headers()
		);
	}
}

(new HttpResponse())->run();