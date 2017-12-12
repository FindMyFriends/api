<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Access;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class OwnedChangeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnForeign() {
		['id' => $id] = (new Misc\SampleEvolution($this->database))->try();
		Assert::exception(function() use ($id) {
			(new Evolution\OwnedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeUser('1000'),
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, sprintf('%d is not your evolution change', $id));
		Assert::exception(function() use ($id) {
			(new Evolution\OwnedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeUser('1000'),
				$this->database
			))->affect([]);
		}, \UnexpectedValueException::class, sprintf('%d is not your evolution change', $id));
		Assert::exception(function() use ($id) {
			(new Evolution\OwnedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeUser('1000'),
				$this->database
			))->revert();
		}, \UnexpectedValueException::class, sprintf('%d is not your evolution change', $id));
	}

	public function testPassingWithOwned() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\OwnedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeUser((string) $seeker),
				$this->database
			);
			$evolution->print(new Output\FakeFormat());
		});
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\OwnedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeUser((string) $seeker),
				$this->database
			);
			$evolution->affect([]);
		});
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\OwnedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeUser((string) $seeker),
				$this->database
			);
			$evolution->revert();
		});
	}
}

(new OwnedChangeTest())->run();