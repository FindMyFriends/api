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

final class JsonResponse extends \Tester\TestCase {
	public function testAddingJsonHeader() {
		Assert::same(
			['Content-Type' => 'application/json; charset=utf8'],
			(new Response\JsonResponse(new Application\FakeResponse(null, [])))->headers()
		);
	}

	public function testJsonHeaderWithPriority() {
		Assert::same(
			['Content-Type' => 'application/json; charset=utf8'],
			(new Response\JsonResponse(
				new Application\FakeResponse(null, ['Content-Type' => 'xx'])
			))->headers()
		);
	}

	public function testAddingOtherHeaders() {
		Assert::same(
			['Content-Type' => 'application/json; charset=utf8', 'Accept' => 'text/xml'],
			(new Response\JsonResponse(
				new Application\FakeResponse(null, ['Accept' => 'text/xml'])
			))->headers()
		);
	}
}

(new JsonResponse())->run();