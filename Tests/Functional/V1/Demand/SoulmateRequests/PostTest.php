<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demand\SoulmateRequests;

use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testErrorOnNotInRefreshInterval() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		$response = json_decode(
			(new V1\Demand\SoulmateRequests\Post(
				new FakeUri('/', 'v1/demands/1/soulmate_requests', []),
				$this->database,
				$this->rabbitMq,
				new Http\FakeRole(true)
			))->template(['demand_id' => $demand])->render(),
			true
		);
		Assert::same(['message' => 'Demand is not refreshable for soulmate yet'], $response);
		Assert::same(HTTP_TOO_MANY_REQUESTS, http_response_code());
	}

	public function testSuccessfulResponse() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand, 'searched_at' => '2015-01-01']))->try();
		$response = json_decode(
			(new V1\Demand\SoulmateRequests\Post(
				new FakeUri('/', 'v1/demands/1/soulmate_requests', []),
				$this->database,
				$this->rabbitMq,
				new Http\FakeRole(true)
			))->template(['demand_id' => $demand])->render(),
			true
		);
		Assert::null($response);
		Assert::same(HTTP_ACCEPTED, http_response_code());
	}
}

(new PostTest())->run();