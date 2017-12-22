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
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class StoredChangeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAffectingWholeForSpecificId() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution(
			$this->database,
			['evolved_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$evolution = new Evolution\StoredChange(1, $this->database);
		$evolution->affect(
			[
				'evolved_at' => '2017-09-16 00:00:00+00',
				'general' => [
					'firstname' => null,
					'lastname' => null,
					'gender' => 'man',
					'race_id' => 1,
				],
				'hair' => [
					'style' => null,
					'color_id' => 1,
					'length' => [
						'value' => null,
						'unit' => null,
					],
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'face' => [
					'care' => null,
					'beard' => [
						'color_id' => 2,
						'length' => [
							'value' => 100,
							'unit' => 'mm',
						],
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
				'hands' => [
					'nails' => [
						'length' => [
							'value' => 5,
							'unit' => null,
						],
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
						'length' => [
							'value' => 5,
							'unit' => null,
						],
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
						'length' => [
							'value' => 10,
							'unit' => 'cm',
						],
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
					'length' => [
						'value' => null,
						'unit' => null,
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
					'race' => ['id' => 1, 'value' => 'asian'],
				],
				'evolved_at' => '2017-09-16 00:00:00+00',
				'id' => 1,
			],
			json_decode($evolution->print(new Output\Json)->serialization(), true)
		);
	}

	public function testAffectingAllRelatedBirthYears() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution(
			$this->database,
			['evolved_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['general' => ['birth_year' => '[1990,1993)']]))->try();
		$evolution = new Evolution\StoredChange(1, $this->database);
		$evolution->affect(
			[
				'evolved_at' => '2017-09-16 00:00:00+00',
				'general' => [
					'firstname' => null,
					'lastname' => null,
					'gender' => 'man',
					'race_id' => 1,
				],
				'hair' => [
					'style' => null,
					'color_id' => 1,
					'length' => [
						'value' => null,
						'unit' => null,
					],
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'face' => [
					'care' => null,
					'beard' => [
						'color_id' => 2,
						'length' => [
							'value' => 1,
							'unit' => 'mm',
						],
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
				'hands' => [
					'nails' => [
						'length' => [
							'value' => 5,
							'unit' => null,
						],
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
		Assert::same(
			[
				['birth_year' => '[1990,1993)'],
				['birth_year' => '[1996,1998)'],
				['birth_year' => '[1996,1998)'],
			],
			(new Storage\NativeQuery(
				$this->database,
				'SELECT birth_year FROM general'
			))->rows()
		);
	}

	public function testReverting() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Evolution\StoredChange($id, $this->database))->revert();
		(new Misc\TableCount($this->database, 'evolutions', 1))->assert();
	}

	/**
	 * @throws \UnexpectedValueException Base evolution can not be reverted
	 */
	public function testThrowingOnRevertingBase() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Evolution\StoredChange($id, $this->database))->revert();
	}
}

(new StoredChangeTest())->run();