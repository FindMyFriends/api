<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class RefreshablePublisherTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotReadyRefresh(): void {
		['id' => $demand] = (new Misc\SampleDemand($this->connection))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand]))->try();
		Assert::exception(function () use ($demand) {
			(new Search\RefreshablePublisher(
				new Search\FakePublisher(),
				$this->connection
			))->publish($demand);
		}, \UnexpectedValueException::class, 'Demand is not refreshable for soulmate yet');
	}

	public function testPassingOnReadyRefresh(): void {
		['id' => $demand] = (new Misc\SampleDemand($this->connection))->try();
		(new Misc\SamplePostgresData(
			$this->connection,
			'soulmate_request',
			['demand_id' => $demand, 'searched_at' => '2015-01-01', 'status' => 'succeed']
		))->try();
		Assert::noError(function () use ($demand) {
			(new Search\RefreshablePublisher(
				new Search\FakePublisher(),
				$this->connection
			))->publish($demand);
		});
	}
}

(new RefreshablePublisherTest())->run();
