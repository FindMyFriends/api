<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class JsonErrorTest extends TestCase\Runtime {
	public function testForcingJsonHeader(): void {
		Assert::same(
			['content-type' => 'application/json; charset=utf8'],
			(new Response\JsonError(new \Exception(), ['Content-Type' => 'xx']))->headers()
		);
	}

	public function testForcingJsonHeaderWithoutCaseSensitivity(): void {
		Assert::same(
			['content-type' => 'application/json; charset=utf8'],
			(new Response\JsonError(new \Exception(), ['content-type' => 'xx']))->headers()
		);
	}

	public function testOtherHeadersWithoutRestriction(): void {
		Assert::same(
			['content-type' => 'application/json; charset=utf8', 'foo' => 'bar'],
			(new Response\JsonError(new \Exception(), ['foo' => 'bar']))->headers()
		);
	}

	public function testTakingStatusCodeFromException(): void {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception('', HTTP_BAD_REQUEST)))->status()
		);
	}

	public function testStatusCodeFromParameterOnUnknownOneFromException(): void {
		Assert::same(
			HTTP_FORBIDDEN,
			(new Response\JsonError(new \Exception(), [], HTTP_FORBIDDEN))->status()
		);
	}

	public function testDefaultStatusCodeAsBadRequest(): void {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception()))->status()
		);
	}

	public function testLowerStatusCodeForClientOrServerErrorOnly(): void {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception('', HTTP_OK)))->status()
		);
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception(), [], HTTP_OK))->status()
		);
	}

	public function testHigherStatusCodeForClientOrServerErrorOnly(): void {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception('', 600)))->status()
		);
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception(), [], 600))->status()
		);
	}

	public function testProperJsonOutput(): void {
		Assert::same(
			['message' => 'Some error'],
			json_decode(
				(new Response\JsonError(new \Exception('Some error')))->body()->serialization(),
				true
			)
		);
	}

	public function testNoContentLeadingToDefaultMessage(): void {
		Assert::same(
			['message' => 'Unknown error, contact support.'],
			json_decode(
				(new Response\JsonError(new \Exception()))->body()->serialization(),
				true
			)
		);
	}

	public function testXssProofContent(): void {
		Assert::same(
			['message' => '&lt;&amp;&gt;"\''],
			json_decode(
				(new Response\JsonError(new \Exception('<&>"\'')))->body()
					->serialization(),
				true
			)
		);
	}
}

(new JsonErrorTest())->run();
