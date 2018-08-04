<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DemandLocationsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testTrackingForDemandChange() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Interaction\DemandLocations(
			$demand,
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
		(new Misc\TableCount($this->database, 'demand_locations', 1))->assert();
	}

	public function testDemandsForChange() {
		['id' => $demand1] = (new Misc\SampleDemand($this->database))->try();
		['id' => $demand2] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_location', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_location', ['demand_id' => $demand2]))->try();
		$locations = (new Interaction\DemandLocations(
			$demand1,
			$this->database
		))->history();
		$location = $locations->current();
		Assert::contains(sprintf('"demand_id": %d', $demand1), $location->print(new Output\Json())->serialization());
		$locations->next();
		Assert::null($locations->current());
	}
}

(new DemandLocationsTest())->run();
