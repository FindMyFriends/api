<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Place;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class OwnedSpotTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned() {
		Assert::exception(function () {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				1,
				new Access\FakeSeeker('1'),
				$this->database
			))->move([]);
		}, \UnexpectedValueException::class, 'Spot does not belong to you.');
		Assert::exception(function () {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				1,
				new Access\FakeSeeker('1'),
				$this->database
			))->forget();
		}, \UnexpectedValueException::class, 'Spot does not belong to you.');
		Assert::exception(function () {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				1,
				new Access\FakeSeeker('1'),
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Spot does not belong to you.');
	}

	public function testPassingOnOwned() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->database, 'spot', ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_spot', ['demand_id' => $demand, 'spot_id' => $spot]))->try();
		Assert::noError(function () use ($spot, $seeker) {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				$spot,
				new Access\FakeSeeker((string) $seeker),
				$this->database
			))->move([]);
		});
		Assert::noError(function () use ($spot, $seeker) {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				$spot,
				new Access\FakeSeeker((string) $seeker),
				$this->database
			))->forget();
		});
		Assert::noError(function () use ($spot, $seeker) {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				$spot,
				new Access\FakeSeeker((string) $seeker),
				$this->database
			))->print(new Output\FakeFormat());
		});
	}
}

(new OwnedSpotTest())->run();
