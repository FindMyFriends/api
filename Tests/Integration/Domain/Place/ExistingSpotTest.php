<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Place;

use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ExistingSpotTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown() {
		$ex = Assert::exception(function() {
			(new Place\ExistingSpot(
				new Place\FakeSpot(),
				1,
				$this->database
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Spot does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Place\ExistingSpot(
				new Place\FakeSpot(),
				1,
				$this->database
			))->move([]);
		}, \UnexpectedValueException::class, 'Spot does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Place\ExistingSpot(
				new Place\FakeSpot(),
				1,
				$this->database
			))->forget();
		}, \UnexpectedValueException::class, 'Spot does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithExisting() {
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'spot'))->try();
		Assert::noError(function() use ($id) {
			$spot = new Place\ExistingSpot(
				new Place\FakeSpot(),
				$id,
				$this->database
			);
			$spot->print(new Output\FakeFormat());
		});
		Assert::noError(function() use ($id) {
			$spot = new Place\ExistingSpot(
				new Place\FakeSpot(),
				$id,
				$this->database
			);
			$spot->move([]);
		});
		Assert::noError(function() use ($id) {
			$spot = new Place\ExistingSpot(
				new Place\FakeSpot(),
				$id,
				$this->database
			);
			$spot->forget();
		});
	}
}

(new ExistingSpotTest())->run();
