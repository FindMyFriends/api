<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class OwnedSoulmateTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned(): void {
		$ex = Assert::exception(function () {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				1,
				new Access\FakeSeeker('1'),
				$this->connection
			))->print(new Output\Json());
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function () {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				1,
				new Access\FakeSeeker('1'),
				$this->connection
			))->clarify([]);
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}
}

(new OwnedSoulmateTest())->run();
