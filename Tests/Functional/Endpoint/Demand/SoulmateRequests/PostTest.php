<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function test429OnNotInRefreshInterval() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		Assert::exception(function() use ($demand) {
			(new Endpoint\Demand\SoulmateRequests\Post(
				new FakeUri('/', 'demands/1/soulmate_requests', []),
				$this->database,
				$this->rabbitMq
			))->response(['demand_id' => $demand]);
		}, \UnexpectedValueException::class, 'Demand is not refreshable for soulmate yet', HTTP_TOO_MANY_REQUESTS);
	}

	public function testSuccessfulResponse() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		$response = (new Endpoint\Demand\SoulmateRequests\Post(
			new FakeUri('/', 'demands/1/soulmate_requests', []),
			$this->database,
			$this->rabbitMq
		))->response(['demand_id' => $demand]);
		Assert::null(json_decode($response->body()->serialization()));
		Assert::same(HTTP_ACCEPTED, $response->status());
	}
}

(new PostTest())->run();
