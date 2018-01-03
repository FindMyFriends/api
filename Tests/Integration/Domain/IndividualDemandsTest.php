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

final class IndividualDemandsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		$demand = (new Domain\IndividualDemands(new Access\FakeUser((string) $seeker), $this->database))->ask(
			[
				'general' => [
					'age' => [
						'from' => 19,
						'to' => 21,
					],
					'firstname' => null,
					'lastname' => null,
					'gender' => 'man',
					'race_id' => 1,
				],
				'hair' => [
					'style' => null,
					'color_id' => 8,
					'length' => [
						'value' => null,
						'unit' => null,
					],
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'beard' => [
					'color_id' => 8,
					'length' => [
						'value' => 1,
						'unit' => null,
					],
					'style' => null,
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
					'shape' => null,
				],
				'body' => [
					'build_id' => 1,
					'skin_color_id' => 8,
					'weight' => 120,
					'height' => 130,
				],
				'location' => [
					'coordinates' => [
						'latitude' => 10.4,
						'longitude' => 50.4,
					],
					'met_at' => [
						'moment' => '2017-01-01 00:00:00+00',
						'timeline_side' => 'sooner',
						'approximation' => 'PT1H',
					],
				],
				'hands' => [
					'nails' => [
						'length' => [
							'value' => null,
							'unit' => null,
						],
						'care' => null,
						'color_id' => 8,
					],
					'vein_visibility' => null,
					'joint_visibility' => null,
					'care' => null,
					'hair' => [
						'color_id' => 8,
						'amount' => null,
					],
				],
			]
		);
		Assert::equal(new Domain\StoredDemand(1, $this->database), $demand);
		(new Misc\TableCounts(
			$this->database,
			[
				'seekers' => 1,
				'locations' => 1,
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

	public function testAllForSpecifiedSeeker() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$demands = (new Domain\IndividualDemands(
			new Access\FakeUser((string) $seeker),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json)->serialization());
		$demands->next();
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json)->serialization());
		$demands->next();
		Assert::null($demands->current());
	}

	public function testCounting() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		Assert::same(
			2,
			(new Domain\IndividualDemands(
				new Access\FakeUser((string) $seeker),
				$this->database
			))->count(new Dataset\FakeSelection(null, []))
		);
	}
}

(new IndividualDemandsTest())->run();