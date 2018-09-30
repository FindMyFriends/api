<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Place;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class OwnedSpotTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned(): void {
		Assert::exception(function () {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				1,
				new Access\FakeSeeker('1'),
				$this->connection
			))->move([]);
		}, \UnexpectedValueException::class, 'Spot does not belong to you.');
		Assert::exception(function () {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				1,
				new Access\FakeSeeker('1'),
				$this->connection
			))->forget();
		}, \UnexpectedValueException::class, 'Spot does not belong to you.');
		Assert::exception(function () {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				1,
				new Access\FakeSeeker('1'),
				$this->connection
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Spot does not belong to you.');
	}

	public function testPassingOnOwned(): void {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->connection, 'spot', ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'demand_spot', ['demand_id' => $demand, 'spot_id' => $spot]))->try();
		Assert::noError(function () use ($spot, $seeker) {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				$spot,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->move([]);
		});
		Assert::noError(function () use ($spot, $seeker) {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				$spot,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->forget();
		});
		Assert::noError(function () use ($spot, $seeker) {
			(new Place\OwnedSpot(
				new Place\FakeSpot(),
				$spot,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->print(new Output\FakeFormat());
		});
	}
}

(new OwnedSpotTest())->run();
