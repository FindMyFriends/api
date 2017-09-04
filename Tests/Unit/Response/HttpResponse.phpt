<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
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
				200,
				['aCCept' => 'text/plain']
			))->headers()
		);
	}

	public function testHeadersAsTwoSeparateWords() {
		Assert::same(
			['Content-Type' => 'text/html'],
			(new Response\HttpResponse(
				new Application\FakeResponse(null, []),
				200,
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
				200,
				['Content-Type' => 'text/html']
			))->headers()
		);
	}

	public function testCustomResponseCode() {
		(new Response\HttpResponse(
			new Application\FakeResponse(
				null,
				[
					'Content-Type' => 'text/plain',
					'Accept' => 'text/xml',
				]
			),
			201
		))->headers();
		Assert::same(201, http_response_code());
	}
}

(new HttpResponse())->run();