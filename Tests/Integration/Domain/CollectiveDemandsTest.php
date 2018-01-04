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
use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CollectiveDemandsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand() {
		(new Misc\SampleDemand($this->database, ['general' => ['gender' => 'man'], 'created_at' => new \DateTime()]))->try();
		(new Misc\SampleDemand($this->database, ['general' => ['gender' => 'woman'], 'created_at' => new \DateTime('2000-01-01')]))->try();
		$demands = (new Domain\CollectiveDemands(
			new Domain\IndividualDemands(new Access\FakeUser('1'), $this->database),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$demand = $demands->current();
		Assert::contains('"gender": "man"', $demand->print(new Output\Json())->serialization());
		$demands->next();
		$demand = $demands->current();
		Assert::contains('"gender": "woman"', $demand->print(new Output\Json())->serialization());
		$demands->next();
		Assert::null($demands->current());
	}

	public function testCounting() {
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		Assert::same(
			3,
			(new Domain\CollectiveDemands(
				new Domain\FakeDemands(),
				$this->database
			))->count(new Dataset\FakeSelection(null, []))
		);
	}
}

(new CollectiveDemandsTest())->run();