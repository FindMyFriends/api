<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PermittedChangeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnForeign() {
		['id' => $id] = (new Misc\SampleEvolution($this->database))->try();
		$ex = Assert::exception(function() use ($id) {
			(new Evolution\PermittedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'You are not permitted to see this evolution change.');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Evolution\PermittedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->database
			))->affect([]);
		}, \UnexpectedValueException::class, 'You are not permitted to see this evolution change.');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() use ($id) {
			(new Evolution\PermittedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker('1000'),
				$this->database
			))->revert();
		}, \UnexpectedValueException::class, 'You are not permitted to see this evolution change.');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithPermitted() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\PermittedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->database
			);
			$evolution->print(new Output\FakeFormat());
		});
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\PermittedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->database
			);
			$evolution->affect([]);
		});
		Assert::noError(function() use ($seeker, $id) {
			$evolution = new Evolution\PermittedChange(
				new Evolution\FakeChange(),
				$id,
				new Access\FakeSeeker((string) $seeker),
				$this->database
			);
			$evolution->revert();
		});
	}
}

(new PermittedChangeTest())->run();
