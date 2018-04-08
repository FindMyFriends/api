<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Domain;

use FindMyFriends\Domain;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Access;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedDemandTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnForeign() {
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$ex = Assert::exception(function() use ($id) {
			(new Domain\OwnedDemand(
				new Domain\FakeDemand(),
				$id,
				new Access\FakeUser('1000'),
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'This is not your demand');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Domain\OwnedDemand(
				new Domain\FakeDemand(),
				$id,
				new Access\FakeUser('1000'),
				$this->database
			))->retract();
		}, \UnexpectedValueException::class, 'This is not your demand');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Domain\OwnedDemand(
				new Domain\FakeDemand(),
				$id,
				new Access\FakeUser('1000'),
				$this->database
			))->reconsider([]);
		}, \UnexpectedValueException::class, 'This is not your demand');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithOwned() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		Assert::noError(function() use ($id, $seeker) {
			$demand = new Domain\OwnedDemand(
				new Domain\FakeDemand(),
				$id,
				new Access\FakeUser((string) $seeker),
				$this->database
			);
			$demand->print(new Output\FakeFormat());
			$demand->retract();
			$demand->reconsider([]);
		});
	}
}

(new OwnedDemandTest())->run();
