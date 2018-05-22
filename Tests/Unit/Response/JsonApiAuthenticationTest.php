<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Http;
use FindMyFriends\Response;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class JsonApiAuthenticationTest extends Tester\TestCase {
	public function testAllowingAccess() {
		Assert::same(
			'allowed',
			(new Response\JsonApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('allowed')),
				new Http\FakeRole(true)
			))->body()->serialization()
		);
	}

	public function testForbiddenStatusCodeForDeniedAccess() {
		Assert::same(
			HTTP_FORBIDDEN,
			(new Response\JsonApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Http\FakeRole(false)
			))->status()
		);
	}

	public function testDefaultMessageOnForbiddenAccess() {
		Assert::same(
			['message' => 'You are not allowed to see the response.'],
			json_decode(
				(new Response\JsonApiAuthentication(
					new Response\PlainResponse(
						new Output\FakeFormat('foo'),
						['foo' => 'bar']
					),
					new Http\FakeRole(false)
				))->body()->serialization(),
				true
			)
		);
	}

	public function testChangingContentTypeOnError() {
		Assert::same(
			[
				'Content-Type' => 'application/json; charset=utf8',
				'Range' => '10',
			],
			(new Response\JsonApiAuthentication(
				new Response\PlainResponse(
					new Output\FakeFormat('foo'),
					[
						'Content-Type' => 'text/plain',
						'Range' => '10',
					]
				),
				new Http\FakeRole(false)
			))->headers()
		);
	}
}

(new JsonApiAuthenticationTest())->run();
