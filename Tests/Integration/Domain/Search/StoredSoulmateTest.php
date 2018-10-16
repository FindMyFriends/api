<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class StoredSoulmateTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testById(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmate_requests (demand_id, status) VALUES
			(?, ?), (?, ?)',
			[
				(new Storage\NativeQuery(
					$this->connection,
					'INSERT INTO soulmates (demand_id, evolution_id, score, is_exposed) VALUES
					(?, ?, 20, TRUE)
					RETURNING demand_id',
					[
						(new Misc\SampleDemand($this->connection))->try()['id'],
						(new Misc\SampleEvolution($this->connection))->try()['id'],
					]
				))->field(),
				'pending',
				(new Storage\NativeQuery(
					$this->connection,
					'INSERT INTO soulmates (demand_id, evolution_id, score, is_exposed) VALUES
					(?, ?, 30, TRUE)
					RETURNING id',
					[
						(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try()['id'],
						(new Misc\SampleEvolution($this->connection))->try()['id'],
					]
				))->field(),
				'pending',
			]
		))->execute();
		$soulmate = json_decode(
			(new Search\StoredSoulmate(
				2,
				$this->connection
			))->print(new Output\Json())->serialization(),
			true
		);
		Assert::same(2, $soulmate['evolution_id']);
		Assert::same(2, $soulmate['demand_id']);
		Assert::same($seeker, $soulmate['seeker_id']);
	}

	public function testClarification(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['is_correct' => true]))->try();
		(new Search\StoredSoulmate(
			$id,
			$this->connection
		))->clarify(false);
		Assert::false((new Storage\TypedQuery($this->connection, 'SELECT is_correct FROM soulmates'))->field());
	}

	public function testExposing(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['is_exposed' => false]))->try();
		(new Search\StoredSoulmate(
			$id,
			$this->connection
		))->expose();
		Assert::true((new Storage\TypedQuery($this->connection, 'SELECT is_exposed FROM soulmates'))->field());
	}
}

(new StoredSoulmateTest())->run();
