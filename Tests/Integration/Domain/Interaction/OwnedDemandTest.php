<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Interaction;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class OwnedDemandTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnForeign() {
		['id' => $id] = (new Misc\SampleDemand($this->connection))->try();
		$ex = Assert::exception(function() use ($id) {
			(new Interaction\OwnedDemand(
				new Interaction\FakeDemand(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->connection
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'This is not your demand');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Interaction\OwnedDemand(
				new Interaction\FakeDemand(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->connection
			))->retract();
		}, \UnexpectedValueException::class, 'This is not your demand');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Interaction\OwnedDemand(
				new Interaction\FakeDemand(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->connection
			))->reconsider([]);
		}, \UnexpectedValueException::class, 'This is not your demand');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithOwned() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		Assert::noError(function() use ($id, $seeker) {
			$demand = new Interaction\OwnedDemand(
				new Interaction\FakeDemand(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			);
			$demand->print(new Output\FakeFormat());
			$demand->retract();
			$demand->reconsider([]);
		});
	}
}

(new OwnedDemandTest())->run();
