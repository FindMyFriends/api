<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Interaction;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class OwnedSpotsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned(): void {
		Assert::exception(function () {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->connection
			))->track([]);
		}, \UnexpectedValueException::class, 'Demand does not belong to you.');
		Assert::exception(function () {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->connection
			))->history();
		}, \UnexpectedValueException::class, 'Demand does not belong to you.');
	}

	public function testPassingOnOwned(): void {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'demand_spot', ['demand_id' => $demand]))->try();
		Assert::noError(function () use ($demand, $seeker) {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$demand,
				$this->connection
			))->track(
				[
					'coordinates' => [
						['latitude' => 50.5, 'longitude' => 50.3],
					],
					'met_at' => [
						'moment' => '2018-01-01 01:01:01',
						'timeline_side' => 'sooner',
						'approximation' => 'PT2H',
					],
				]
			);
		});
		Assert::noError(function () use ($demand, $seeker) {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$demand,
				$this->connection
			))->history();
		});
	}
}

(new OwnedSpotsTest())->run();
