<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class RefreshablePublisherTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotReadyRefresh() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		Assert::exception(function () use ($demand) {
			(new Search\RefreshablePublisher(
				new Search\FakePublisher(),
				$this->database
			))->publish($demand);
		}, \UnexpectedValueException::class, 'Demand is not refreshable for soulmate yet');
	}

	public function testPassingOnReadyRefresh() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData(
			$this->database,
			'soulmate_request',
			['demand_id' => $demand, 'searched_at' => '2015-01-01', 'status' => 'succeed']
		))->try();
		Assert::noError(function () use ($demand) {
			(new Search\RefreshablePublisher(
				new Search\FakePublisher(),
				$this->database
			))->publish($demand);
		});
	}
}

(new RefreshablePublisherTest())->run();
