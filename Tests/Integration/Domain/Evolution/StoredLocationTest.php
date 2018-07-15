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
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class StoredLocationTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testForgettingByLocation() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $location] = (new Misc\SamplePostgresData($this->database, 'location'))->try();
		(new Misc\SamplePostgresData($this->database, 'evolution_location', ['evolution_id' => $change, 'location_id' => $location]))->try();
		(new Misc\TableCount($this->database, 'evolution_locations', 1))->assert();
		(new Evolution\StoredLocation(
			$location,
			$this->database
		))->forget();
		(new Misc\TableCount($this->database, 'evolution_locations', 0))->assert();
		Assert::count(
			0,
			(new Storage\NativeQuery(
				$this->database,
				'SELECT * FROM locations WHERE id = ?',
				[$location]
			))->rows()
		);
	}
}

(new StoredLocationTest())->run();
