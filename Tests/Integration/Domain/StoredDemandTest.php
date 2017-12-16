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
					'race_id' => 1,
				],
				'hair' => [
					'style' => null,
					'color_id' => 1,
					'length' => null,
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'face' => [
					'care' => null,
					'beard' => [
						'color_id' => 2,
						'length' => 1,
						'style' => null,
					],
					'eyebrow' => [
						'color_id' => 3,
						'care' => 5,
					],
					'freckles' => null,
					'eye' => [
						'left' => [
							'color_id' => 4,
							'lenses' => false,
						],
						'right' => [
							'color_id' => 4,
							'lenses' => false,
						],
					],
					'shape' => null,
					'teeth' => [
						'care' => 10,
						'braces' => true,
					],
				],
				'body' => [
					'build_id' => 1,
					'skin_color_id' => 6,
					'weight' => 120,
					'height' => 130,
				],
				'location' => [
					'coordinates' => [
						'latitude' => 10.4,
						'longitude' => 50.4,
					],
					'met_at' => [
						'from' => '2017-01-01 00:00:00+00',
						'to' => '2017-01-02 00:00:00+00',
					],
				],
				'hands' => [
					'nails' => [
						'length' => null,
						'care' => null,
						'color_id' => 2,
					],
					'vein_visibility' => null,
					'joint_visibility' => null,
					'care' => null,
					'hair' => [
						'color_id' => 3,
						'amount' => null,
					],
				],
			]
		);
		Assert::equal(
			[
				'hands' => [
					'nails' => [
						'length' => null,
						'care' => null,
						'color' => ['id' => 2, 'hex' => 'faebd7', 'name' => 'AntiqueWhite'],
					],
					'vein_visibility' => null,
					'joint_visibility' => null,
					'care' => null,
					'hair' => [
						'color' => ['id' => 3, 'hex' => '00ffff', 'name' => 'Aqua'],
						'amount' => null,
					],
				],
				'location' => [
					'coordinates' => ['latitude' => 10.4, 'longitude' => 50.4],
					'met_at' => ['to' => '2017-01-02 00:00:00.000000+0000', 'from' => '2017-01-01 00:00:00.000000+0000'],
				],
				'body' => [
					'build' => ['id' => 1, 'value' => 'skinny'],
					'skin_color' => ['id' => 6, 'hex' => 'f5f5dc', 'name' => 'Beige'],
					'weight' => 120,
					'height' => 130,
				],
				'face' => [
					'care' => null,
					'beard' => [
						'id' => 3,
						'length' => 1,
						'style' => null,
						'color' => ['id' => 2, 'hex' => 'faebd7', 'name' => 'AntiqueWhite'],
					],
					'eyebrow' => [
						'id' => 3,
						'care' => 5,
						'color' => ['id' => 3, 'hex' => '00ffff', 'name' => 'Aqua'],
					],
					'freckles' => null,
					'eye' => [
						'left' => [
							'id' => 5,
							'lenses' => false,
							'color' => ['id' => 4, 'hex' => '7fffd4', 'name' => 'Aquamarine'],
						],
						'right' => [
							'id' => 6,
							'lenses' => false,
							'color' => ['id' => 4, 'hex' => '7fffd4', 'name' => 'Aquamarine'],
						],
					],
					'shape' => null,
					'teeth' => ['id' => 3, 'care' => 10, 'braces' => true],
				],
				'hair' => [
					'style' => null,
					'color' => ['id' => 1, 'hex' => 'f0f8ff', 'name' => 'AliceBlue'],
					'length' => null,
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'general' => [
					'age' => ['to' => 21, 'from' => 19],
					'firstname' => null,
					'lastname' => null,
					'gender' => 'man',
					'race' => ['id' => 1, 'value' => 'asian'],
				],
				'created_at' => '2017-09-16 00:00:00+00',
				'seeker_id' => $seeker,
				'id' => 1,
			],
			json_decode($demand->print(new Output\Json)->serialization(), true)
		);
	}
}

(new StoredDemandTest())->run();