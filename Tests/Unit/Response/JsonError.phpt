<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class JsonError extends \Tester\TestCase {
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
			400,
			(new Response\JsonError(new \Exception('', 400)))->status()
		);
	}

	public function testStatusCodeFromParameterOnUnknownOneFromException() {
		Assert::same(
			403,
			(new Response\JsonError(new \Exception(), [], 403))->status()
		);
	}

	public function testDefaultStatusCodeAsBadRequest() {
		Assert::same(
			400,
			(new Response\JsonError(new \Exception()))->status()
		);
	}

	public function testLowerStatusCodeForClientOrServerErrorOnly() {
		Assert::same(
			400,
			(new Response\JsonError(new \Exception('', 200)))->status()
		);
		Assert::same(
			400,
			(new Response\JsonError(new \Exception(), [], 200))->status()
		);
	}

	public function testHigherStatusCodeForClientOrServerErrorOnly() {
		Assert::same(
			400,
			(new Response\JsonError(new \Exception('', 600)))->status()
		);
		Assert::same(
			400,
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
			['message' => '&lt;&amp;&gt;&quot;&apos;'],
			json_decode(
				(new Response\JsonError(new \Exception('<&>"\'')))->body()
					->serialization(),
				true
			)
		);
	}
}

(new JsonError())->run();