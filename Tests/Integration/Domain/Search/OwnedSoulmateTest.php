<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class OwnedSoulmateTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnForeignSoulmate(): void {
		$ex = Assert::exception(function () {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				1,
				new Access\FakeSeeker('2'),
				$this->connection
			))->print(new Output\Json());
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function () {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				1,
				new Access\FakeSeeker('2'),
				$this->connection
			))->clarify([]);
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingOnDemandOwner(): void {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection))->try();
		['id' => $evolution] = (new Misc\SampleEvolution($this->connection))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $soulmate] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand, 'evolution_id' => $evolution]))->try();
		Assert::noError(function () use ($soulmate, $seeker) {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				$soulmate,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->print(new Output\Json());
		});
		Assert::noError(function () use ($soulmate, $seeker) {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				$soulmate,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->clarify([]);
		});
	}

	public function testForbiddingEvolvingFields(): void {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection))->try();
		['id' => $evolution] = (new Misc\SampleEvolution($this->connection))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $soulmate] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand, 'evolution_id' => $evolution]))->try();
		Assert::exception(function () use ($soulmate, $seeker) {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				$soulmate,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->clarify(['is_exposed' => true]);
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::noError(function () use ($soulmate, $seeker) {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				$soulmate,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->clarify(['is_correct' => false]);
		});
	}

	public function testThrowingOnChangingDemandingForeignField(): void {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection))->try();
		['id' => $evolution] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection))->try();
		['id' => $soulmate] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand, 'evolution_id' => $evolution]))->try();
		Assert::exception(function () use ($soulmate, $seeker) {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				$soulmate,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->clarify(['is_correct' => true]);
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::noError(function () use ($soulmate, $seeker) {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				$soulmate,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->clarify(['is_exposed' => false]);
		});
	}
}

(new OwnedSoulmateTest())->run();
