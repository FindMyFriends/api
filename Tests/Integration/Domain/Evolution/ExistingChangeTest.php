<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ExistingChangeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown() {
		$ex = Assert::exception(function() {
			(new Evolution\ExistingChange(
				new Evolution\FakeChange(),
				1,
				$this->connection
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Evolution change does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Evolution\ExistingChange(
				new Evolution\FakeChange(),
				1,
				$this->connection
			))->affect([]);
		}, \UnexpectedValueException::class, 'Evolution change does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Evolution\ExistingChange(
				new Evolution\FakeChange(),
				1,
				$this->connection
			))->revert();
		}, \UnexpectedValueException::class, 'Evolution change does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithExisting() {
		(new Misc\SampleEvolution($this->connection))->try();
		Assert::noError(function() {
			$evolution = new Evolution\ExistingChange(
				new Evolution\FakeChange(),
				1,
				$this->connection
			);
			$evolution->print(new Output\FakeFormat());
		});
		Assert::noError(function() {
			$evolution = new Evolution\ExistingChange(
				new Evolution\FakeChange(),
				1,
				$this->connection
			);
			$evolution->affect([]);
		});
		Assert::noError(function() {
			$evolution = new Evolution\ExistingChange(
				new Evolution\FakeChange(),
				1,
				$this->connection
			);
			$evolution->revert();
		});
	}
}

(new ExistingChangeTest())->run();
