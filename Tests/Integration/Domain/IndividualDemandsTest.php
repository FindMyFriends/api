<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IndividualDemandsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		$id = (new Interaction\IndividualDemands(new Access\FakeSeeker((string) $seeker), $this->database))->ask(
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
					'length' => [
						'value' => 5,
						'unit' => 'mm',
					],
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'beard' => [
					'color_id' => 8,
					'length' => [
						'value' => 1,
						'unit' => 'mm',
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
					'shape_id' => null,
				],
				'body' => [
					'build_id' => 1,
					'weight' => [
						'value' => 120,
						'unit' => 'kg',
					],
					'height' => [
						'value' => 130,
						'unit' => 'cm',
					],
					'breast_size' => 'B',
				],
				'hands' => [
					'nails' => [
						'length' => [
							'value' => 5,
							'unit' => 'mm',
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
		Assert::same(1, $id);
		(new Misc\TableCounts(
			$this->database,
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

	public function testAllForSpecifiedSeeker() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$demands = (new Interaction\IndividualDemands(
			new Access\FakeSeeker((string) $seeker),
			$this->database
		))->all(new Dataset\FakeSelection([]));
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json())->serialization());
		$demands->next();
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json())->serialization());
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
			(new Interaction\IndividualDemands(
				new Access\FakeSeeker((string) $seeker),
				$this->database
			))->count(new Dataset\FakeSelection([]))
		);
	}
}

(new IndividualDemandsTest())->run();
