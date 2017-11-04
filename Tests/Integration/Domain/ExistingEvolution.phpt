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

final class ExistingEvolution extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown() {
		Assert::exception(function() {
			(new Domain\ExistingEvolution(
				new Domain\FakeEvolution(),
				1,
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Evolution 1 does not exist');
	}

	public function testPassingWithExisting() {
		(new Misc\SampleEvolution($this->database))->try();
		Assert::noError(function() {
			$evolution = new Domain\ExistingEvolution(
				new Domain\FakeEvolution(),
				1,
				$this->database
			);
			$evolution->print(new Output\FakeFormat());
		});
	}
}

(new ExistingEvolution())->run();