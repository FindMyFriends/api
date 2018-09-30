<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class VisibleChangeTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnForeign(): void {
		['id' => $id] = (new Misc\SampleEvolution($this->connection))->try();
		$ex = Assert::exception(function() use ($id) {
			(new Evolution\VisibleChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->connection
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Evolution\VisibleChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->connection
			))->affect([]);
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Evolution\VisibleChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->connection
			))->revert();
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithOwned(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\VisibleChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			);
			$evolution->print(new Output\FakeFormat());
		});
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\VisibleChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			);
			$evolution->affect([]);
		});
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\VisibleChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			);
			$evolution->revert();
		});
	}
}

(new VisibleChangeTest())->run();
