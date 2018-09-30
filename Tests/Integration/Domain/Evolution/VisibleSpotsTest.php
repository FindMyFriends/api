<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class VisibleSpotsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned(): void {
		Assert::exception(function () {
			(new Evolution\VisibleSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->connection
			))->track([]);
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
		Assert::exception(function () {
			(new Evolution\VisibleSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker('1'),
				1,
				$this->connection
			))->history();
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
	}

	public function testPassingOnOwned(): void {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'evolution_spot', ['evolution_id' => $change]))->try();
		Assert::noError(function () use ($change, $seeker) {
			(new Evolution\VisibleSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$change,
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
		Assert::noError(function () use ($change, $seeker) {
			(new Evolution\VisibleSpots(
				new Place\FakeSpots(),
				new Access\FakeSeeker((string) $seeker),
				$change,
				$this->connection
			))->history();
		});
	}
}

(new VisibleSpotsTest())->run();
