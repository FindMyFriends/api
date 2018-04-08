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
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredDemandTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRemovingSingleDemand() {
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Domain\StoredDemand(1, $this->database))->retract();
		(new Misc\TableCount($this->database, 'demands', 1))->assert();
		Assert::same(
			2,
			(new Storage\NativeQuery(
				$this->database,
				'SELECT id FROM demands'
			))->field()
		);
	}

	public function testReconsideringAsWholeForSpecificId() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleDemand(
			$this->database,
			['created_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleDemand($this->database))->try();
		$demand = new Domain\StoredDemand(1, $this->database);
		$demand->reconsider(
			[
				'general' => [
					'age' => [
						'from' => 19,
						'to' => 21,
					],
					'firstname' => null,
					'lastname' => null,
					'gender' => 'man',
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
				'eyebrow' => [
					'color_id' => 8,
					'care' => 5,
				],
				'face' => [
					'care' => null,
					'freckles' => null,
					'shape_id' => 1,
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
		Assert::equal(
			[
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
				'location' => [
					'coordinates' => ['latitude' => 10.4, 'longitude' => 50.4],
					'met_at' => [
						'moment' => '2017-01-01T00:00:00+00:00',
						'timeline_side' => 'sooner',
						'approximation' => 'PT1H',
					],
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
				'beard' => [
					'length' => [
						'value' => 1,
						'unit' => 'mm',
					],
					'style' => null,
					'color_id' => 8,
				],
				'eyebrow' => [
					'care' => 5,
					'color_id' => 8,
				],
				'eye' => [
					'left' => [
						'lenses' => false,
						'color_id' => 8,
					],
					'right' => [
						'lenses' => false,
						'color_id' => 8,
					],
				],
				'teeth' => ['care' => 10, 'braces' => true],
				'face' => [
					'care' => null,
					'freckles' => null,
					'shape_id' => 1,
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
				'general' => [
					'age' => ['to' => 21, 'from' => 19],
					'firstname' => null,
					'lastname' => null,
					'gender' => 'man',
					'ethnic_group_id' => 1,
				],
				'created_at' => '2017-09-16 00:00:00+00',
				'seeker_id' => $seeker,
				'id' => 1,
			],
			json_decode($demand->print(new Output\Json())->serialization(), true)
		);
	}
}

(new StoredDemandTest())->run();
