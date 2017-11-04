<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class JsonApiAuthentication extends Tester\TestCase {
	public function testAllowingAccess() {
		Assert::same(
			'allowed',
			(new Response\JsonApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('allowed')),
				new Access\FakeUser('1', ['role' => 'guest']),
				new Uri\FakeUri(null, '/v1/demands')
			))->body()->serialization()
		);
	}

	public function testProvidingDefaultRole() {
		Assert::same(
			'allowed',
			(new Response\JsonApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('allowed'), ['foo' => 'bar']),
				new Access\FakeUser('1', []),
				new Uri\FakeUri(null, '/v1/demands')
			))->body()->serialization()
		);
	}

	public function testForbiddenStatusCodeForDeniedAccess() {
		Assert::same(
			403,
			(new Response\JsonApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser('1', ['role' => 'guest']),
				new Uri\FakeUri(null, 'foo')
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
					new Access\FakeUser('1', ['role' => 'guest']),
					new Uri\FakeUri(null, 'foo')
				))->body()->serialization(),
				true
			)
		);
	}
}

(new JsonApiAuthentication())->run();