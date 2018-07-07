<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Evolutions;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\Schema\Evolution;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Evolutions\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'evolutions', []),
			$this->database,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		$demands = json_decode($response->body()->serialization());
		Assert::count(2, $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/Endpoint/Evolution/schema/get.json')
		))->assert();
	}

	public function testMatchingSorts() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$this->database->exec('REFRESH MATERIALIZED VIEW prioritized_evolution_fields');
		Assert::same(
			[],
			array_diff(
				array_keys(
					(new Evolution\PrioritizedColumns(
						$this->database,
						new Access\FakeSeeker((string) $seeker)
					))->values()
				),
				Endpoint\Evolutions\Get::SORTS
			)
		);
	}
}

(new GetTest())->run();
