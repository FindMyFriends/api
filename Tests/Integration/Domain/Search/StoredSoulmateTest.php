<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class StoredSoulmateTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testById() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmate_requests (demand_id, status) VALUES
			(?, ?), (?, ?)',
			[
				(new Storage\NativeQuery(
					$this->database,
					'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES
					(?, ?, 20)
					RETURNING demand_id',
					[
						(new Misc\SampleDemand($this->database))->try()['id'],
						(new Misc\SampleEvolution($this->database))->try()['id'],
					]
				))->field(),
				'pending',
				(new Storage\NativeQuery(
					$this->database,
					'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES
					(?, ?, 30)
					RETURNING id',
					[
						(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try()['id'],
						(new Misc\SampleEvolution($this->database))->try()['id'],
					]
				))->field(),
				'pending',
			]
		))->execute();
		$soulmate = json_decode(
			(new Search\StoredSoulmate(
				2,
				$this->database,
				new Access\FakeSeeker('1')
			))->print(new Output\Json())->serialization(),
			true
		);
		Assert::same(2, $soulmate['evolution_id']);
		Assert::same(2, $soulmate['demand_id']);
		Assert::same($seeker, $soulmate['seeker_id']);
	}

	public function testClarification() {
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['is_correct' => true]))->try();
		(new Search\StoredSoulmate(
			$id,
			$this->database,
			new Access\FakeSeeker((string) mt_rand())
		))->clarify(['is_correct' => false]);
		Assert::false((new Storage\TypedQuery($this->database, 'SELECT is_correct FROM soulmates'))->field());
	}
}

(new StoredSoulmateTest())->run();
