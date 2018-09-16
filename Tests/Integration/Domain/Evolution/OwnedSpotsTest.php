<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class OwnedSpotsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned() {
		Assert::exception(function () {
			(new Evolution\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->database
			))->track([]);
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
		Assert::exception(function () {
			(new Evolution\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->database
			))->history();
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
	}

	public function testPassingOnOwned() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->database, 'evolution_spot', ['evolution_id' => $change]))->try();
		Assert::noError(function () use ($change, $seeker) {
			(new Evolution\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$change,
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
		Assert::noError(function () use ($change, $seeker) {
			(new Evolution\OwnedSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$change,
				$this->database
			))->history();
		});
	}
}

(new OwnedSpotsTest())->run();
