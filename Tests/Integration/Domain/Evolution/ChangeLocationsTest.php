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
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ChangeLocationsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testTrackingForEvolutionChange() {
		['id' => $change] = (new Misc\SampleEvolution($this->database))->try();
		(new Evolution\ChangeLocations(
			$change,
			$this->database
		))->track(
			[
				'coordinates' => [
					'latitude' => 50.5,
					'longitude' => 50.3,
				],
				'met_at' => [
					'moment' => '2018-01-01 01:01:01',
					'timeline_side' => 'sooner',
					'approximation' => 'PT2H',
				],
			]
		);
		(new Misc\TableCount($this->database, 'locations', 1))->assert();
		(new Misc\TableCount($this->database, 'evolution_locations', 1))->assert();
	}

	public function testEvolutionsForChange() {
		['id' => $change1] = (new Misc\SampleEvolution($this->database))->try();
		['id' => $change2] = (new Misc\SampleEvolution($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'evolution_location', ['evolution_id' => $change1]))->try();
		(new Misc\SamplePostgresData($this->database, 'evolution_location', ['evolution_id' => $change2]))->try();
		$locations = (new Evolution\ChangeLocations(
			$change1,
			$this->database
		))->history();
		$location = $locations->current();
		Assert::contains(sprintf('"evolution_id": %d', $change1), $location->print(new Output\Json())->serialization());
		$locations->next();
		Assert::null($locations->current());
	}
}

(new ChangeLocationsTest())->run();