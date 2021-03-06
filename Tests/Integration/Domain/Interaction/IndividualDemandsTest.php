<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Interaction;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class IndividualDemandsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		$id = (new Interaction\IndividualDemands(new Access\FakeSeeker((string) $seeker), $this->connection))->ask(
			[
				'note' => null,
				'general' => [
					'age' => [
						'from' => 19,
						'to' => 21,
					],
					'firstname' => null,
					'lastname' => null,
					'sex' => 'man',
					'ethnic_group_id' => 1,
				],
				'hair' => [
					'style_id' => 1,
					'color_id' => 8,
					'length_id' => 1,
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'beard' => [
					'color_id' => 8,
					'length_id' => 1,
					'style_id' => null,
				],
				'eyebrow' => [
					'color_id' => 8,
					'care' => 5,
				],
				'eye' => [
					'left' => [
						'color_id' => 8,
						'lenses' => false,
					],
					'right' => [
						'color_id' => 8,
						'lenses' => false,
					],
				],
				'teeth' => [
					'care' => 10,
					'braces' => true,
				],
				'face' => [
					'care' => null,
					'freckles' => null,
					'shape_id' => null,
				],
				'body' => [
					'build_id' => 1,
					'breast_size' => 'B',
				],
				'hands' => [
					'nails' => [
						'length_id' => 1,
						'color_id' => 8,
					],
					'visible_veins' => null,
					'care' => null,
				],
			]
		);
		Assert::same(1, $id);
		(new Misc\TableCounts(
			$this->connection,
			[
				'seekers' => 1,
				'descriptions' => 1,
				'demands' => 1,
				'bodies' => 1,
				'general' => 1,
				'faces' => 1,
				'hands' => 1,
				'eyes' => 2,
				'hair' => 1,
				'nails' => 1,
				'teeth' => 1,
				'beards' => 1,
				'eyebrows' => 1,
				'hand_hair' => 1,
			]
		))->assert();
	}

	public function testAllForSpecifiedSeeker(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		$demands = (new Interaction\IndividualDemands(
			new Access\FakeSeeker((string) $seeker),
			$this->connection
		))->all(new Dataset\FakeSelection([]));
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json())->serialization());
		$demands->next();
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json())->serialization());
		$demands->next();
		Assert::null($demands->current());
	}

	public function testCounting(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		Assert::same(
			2,
			(new Interaction\IndividualDemands(
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->count(new Dataset\FakeSelection([]))
		);
	}
}

(new IndividualDemandsTest())->run();
