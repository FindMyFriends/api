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
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ExistingDemandTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown() {
		Assert::exception(function() {
			(new Domain\ExistingDemand(
				new Domain\FakeDemand(),
				1,
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Demand 1 does not exist');
		Assert::exception(function() {
			(new Domain\ExistingDemand(
				new Domain\FakeDemand(),
				1,
				$this->database
			))->retract();
		}, \UnexpectedValueException::class, 'Demand 1 does not exist');
		Assert::exception(function() {
			(new Domain\ExistingDemand(
				new Domain\FakeDemand(),
				1,
				$this->database
			))->reconsider([]);
		}, \UnexpectedValueException::class, 'Demand 1 does not exist');
	}

	public function testPassingWithExisting() {
		(new Misc\SampleDemand($this->database))->try();
		Assert::noError(function() {
			$demand = new Domain\ExistingDemand(new Domain\FakeDemand(), 1, $this->database);
			$demand->print(new Output\FakeFormat());
			$demand->retract();
			$demand->reconsider([]);
		});
	}
}

(new ExistingDemandTest())->run();