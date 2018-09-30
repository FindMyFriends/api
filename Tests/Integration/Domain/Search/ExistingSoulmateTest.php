<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ExistingSoulmateTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknown() {
		$ex = Assert::exception(function () {
			(new Search\ExistingSoulmate(
				new Search\FakeSoulmate(),
				1,
				$this->connection
			))->print(new Output\Json());
		}, \UnexpectedValueException::class, 'Soulmate does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
		$ex = Assert::exception(function () {
			(new Search\ExistingSoulmate(
				new Search\FakeSoulmate(),
				1,
				$this->connection
			))->clarify([]);
		}, \UnexpectedValueException::class, 'Soulmate does not exist');
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}
}

(new ExistingSoulmateTest())->run();
