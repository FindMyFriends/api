<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri\FakeUri;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class PostTest extends TestCase\Runtime {
	use TestCase\Page;

	public function test429OnNotInRefreshInterval(): void {
		['id' => $demand] = (new Misc\SampleDemand($this->connection))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand]))->try();
		Assert::exception(function() use ($demand) {
			(new Endpoint\Demand\SoulmateRequests\Post(
				new FakeUri('/', 'demands/1/soulmate_requests', []),
				$this->connection,
				$this->rabbitMq
			))->response(['demand_id' => $demand]);
		}, \UnexpectedValueException::class, 'Demand is not refreshable for soulmate yet', HTTP_TOO_MANY_REQUESTS);
	}

	public function testSuccessfulResponse(): void {
		['id' => $demand] = (new Misc\SampleDemand($this->connection))->try();
		$response = (new Endpoint\Demand\SoulmateRequests\Post(
			new FakeUri('/', 'demands/1/soulmate_requests', []),
			$this->connection,
			$this->rabbitMq
		))->response(['demand_id' => $demand]);
		Assert::null(json_decode($response->body()->serialization()));
		Assert::same(HTTP_ACCEPTED, $response->status());
	}
}

(new PostTest())->run();
