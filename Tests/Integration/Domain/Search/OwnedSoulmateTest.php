<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class OwnedSoulmateTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnNotOwned() {
		$ex = Assert::exception(function () {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				1,
				new Access\FakeSeeker('1'),
				$this->database
			))->print(new Output\Json());
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function () {
			(new Search\OwnedSoulmate(
				new Search\FakeSoulmate(),
				1,
				new Access\FakeSeeker('1'),
				$this->database
			))->clarify([]);
		}, \UnexpectedValueException::class, 'This is not your soulmate');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}
}

(new OwnedSoulmateTest())->run();
