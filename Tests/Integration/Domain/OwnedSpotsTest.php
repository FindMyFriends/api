<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSpotsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned() {
		Assert::exception(function () {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->database
			))->track([]);
		}, \UnexpectedValueException::class, 'Demand does not belong to you.');
		Assert::exception(function () {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->database
			))->history();
		}, \UnexpectedValueException::class, 'Demand does not belong to you.');
	}

	public function testPassingOnOwned() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_location', ['demand_id' => $demand]))->try();
		Assert::noError(function () use ($demand, $seeker) {
			(new Interaction\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$demand,
				$this->database
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
				$this->database
			))->history();
		});
	}
}

(new OwnedSpotsTest())->run();
