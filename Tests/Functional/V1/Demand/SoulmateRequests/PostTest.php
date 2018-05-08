<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demand\SoulmateRequests;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function test429OnNotInRefreshInterval() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		$response = (new V1\Demand\SoulmateRequests\Post(
			new FakeUri('/', 'v1/demands/1/soulmate_requests', []),
			$this->database,
			$this->rabbitMq
		))->response(['demand_id' => $demand]);
		Assert::same(['message' => 'Demand is not refreshable for soulmate yet'], json_decode($response->body()->serialization(), true));
		Assert::same(HTTP_TOO_MANY_REQUESTS, $response->status());
	}

	public function testSuccessfulResponse() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		$response = (new V1\Demand\SoulmateRequests\Post(
			new FakeUri('/', 'v1/demands/1/soulmate_requests', []),
			$this->database,
			$this->rabbitMq
		))->response(['demand_id' => $demand]);
		Assert::null(json_decode($response->body()->serialization()));
		Assert::same(HTTP_ACCEPTED, $response->status());
	}
}

(new PostTest())->run();
