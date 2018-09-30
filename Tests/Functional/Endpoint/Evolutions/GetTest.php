<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Evolutions;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\Schema\Evolution;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		(new Misc\SampleEvolution($this->connection))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker, 'general' => ['birth_year' => 1996]]))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker, 'general' => ['birth_year' => 1998]]))->try();
		$response = (new Endpoint\Evolutions\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'evolutions', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member'])
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		$demands = json_decode($response->body()->serialization());
		Assert::count(2, $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/Endpoint/Evolution/schema/get.json')
		))->assert();
	}

	public function testIncludedCountHeader(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		$headers = (new Endpoint\Evolutions\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'evolutions', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => ''])->headers();
		Assert::same(2, $headers['X-Total-Count']);
	}

	public function testMatchingSorts(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		$this->connection->exec('REFRESH MATERIALIZED VIEW prioritized_evolution_fields');
		Assert::same(
			[],
			array_diff(
				array_keys(
					(new Evolution\PrioritizedColumns(
						$this->connection,
						new Access\FakeSeeker((string) $seeker)
					))->values()
				),
				Endpoint\Evolutions\Get::SORTS
			)
		);
	}
}

(new GetTest())->run();
