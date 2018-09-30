<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Place;

use FindMyFriends\Domain\Place;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ExistingSpotTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown(): void {
		$ex = Assert::exception(function() {
			(new Place\ExistingSpot(
				new Place\FakeSpot(),
				1,
				$this->connection
			))->print(new Output\FakeFormat());
		}, \UnexpectedValueException::class, 'Spot does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Place\ExistingSpot(
				new Place\FakeSpot(),
				1,
				$this->connection
			))->move([]);
		}, \UnexpectedValueException::class, 'Spot does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Place\ExistingSpot(
				new Place\FakeSpot(),
				1,
				$this->connection
			))->forget();
		}, \UnexpectedValueException::class, 'Spot does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testPassingWithExisting(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'spot'))->try();
		Assert::noError(function() use ($id) {
			$spot = new Place\ExistingSpot(
				new Place\FakeSpot(),
				$id,
				$this->connection
			);
			$spot->print(new Output\FakeFormat());
		});
		Assert::noError(function() use ($id) {
			$spot = new Place\ExistingSpot(
				new Place\FakeSpot(),
				$id,
				$this->connection
			);
			$spot->move([]);
		});
		Assert::noError(function() use ($id) {
			$spot = new Place\ExistingSpot(
				new Place\FakeSpot(),
				$id,
				$this->connection
			);
			$spot->forget();
		});
	}
}

(new ExistingSpotTest())->run();
