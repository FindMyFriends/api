<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class JsonErrorTest extends Tester\TestCase {
	public function testForcingJsonHeader() {
		Assert::same(
			['content-type' => 'application/json; charset=utf8'],
			(new Response\JsonError(new \Exception(), ['Content-Type' => 'xx']))->headers()
		);
	}

	public function testForcingJsonHeaderWithoutCaseSensitivity() {
		Assert::same(
			['content-type' => 'application/json; charset=utf8'],
			(new Response\JsonError(new \Exception(), ['content-type' => 'xx']))->headers()
		);
	}

	public function testOtherHeadersWithoutRestriction() {
		Assert::same(
			['content-type' => 'application/json; charset=utf8', 'foo' => 'bar'],
			(new Response\JsonError(new \Exception(), ['foo' => 'bar']))->headers()
		);
	}

	public function testTakingStatusCodeFromException() {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception('', HTTP_BAD_REQUEST)))->status()
		);
	}

	public function testStatusCodeFromParameterOnUnknownOneFromException() {
		Assert::same(
			HTTP_FORBIDDEN,
			(new Response\JsonError(new \Exception(), [], HTTP_FORBIDDEN))->status()
		);
	}

	public function testDefaultStatusCodeAsBadRequest() {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception()))->status()
		);
	}

	public function testLowerStatusCodeForClientOrServerErrorOnly() {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception('', HTTP_OK)))->status()
		);
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception(), [], HTTP_OK))->status()
		);
	}

	public function testHigherStatusCodeForClientOrServerErrorOnly() {
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception('', 600)))->status()
		);
		Assert::same(
			HTTP_BAD_REQUEST,
			(new Response\JsonError(new \Exception(), [], 600))->status()
		);
	}

	public function testProperJsonOutput() {
		Assert::same(
			['message' => 'Some error'],
			json_decode(
				(new Response\JsonError(new \Exception('Some error')))->body()->serialization(),
				true
			)
		);
	}

	public function testNoContentLeadingToDefaultMessage() {
		Assert::same(
			['message' => 'Unknown error, contact support.'],
			json_decode(
				(new Response\JsonError(new \Exception()))->body()->serialization(),
				true
			)
		);
	}

	public function testXssProofContent() {
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
