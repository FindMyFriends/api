<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Interaction;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ExistingDemandTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown(): void {
		$ex = Assert::exception(function() {
			(new Interaction\ExistingDemand(
				new Interaction\FakeDemand(),
				1,
				$this->connection
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Demand does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Interaction\ExistingDemand(
				new Interaction\FakeDemand(),
				1,
				$this->connection
			))->retract();
		}, \UnexpectedValueException::class, 'Demand does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Interaction\ExistingDemand(
				new Interaction\FakeDemand(),
				1,
				$this->connection
			))->reconsider([]);
		}, \UnexpectedValueException::class, 'Demand does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithExisting(): void {
		(new Misc\SampleDemand($this->connection))->try();
		Assert::noError(function() {
			$demand = new Interaction\ExistingDemand(new Interaction\FakeDemand(), 1, $this->connection);
			$demand->print(new Output\FakeFormat());
			$demand->retract();
			$demand->reconsider([]);
		});
	}
}

(new ExistingDemandTest())->run();
