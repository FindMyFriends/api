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
					'ethnic_group_id' => 1,
				],
				'hair' => [
					'style_id' => 1,
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
						'value' => 100,
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
					'shape' => null,
				],
				'body' => [
					'build_id' => 1,
					'skin_color_id' => 8,
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
		Assert::equal(
			[
				'hands' => [
					'nails' => [
						'length' => [
							'value' => 5,
							'unit' => null,
						],
						'care' => null,
						'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
					],
					'vein_visibility' => null,
					'joint_visibility' => null,
					'care' => null,
					'hair' => [
						'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
						'amount' => null,
					],
				],
				'body' => [
					'build' => ['id' => 1, 'name' => 'muscular'],
					'skin_color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
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
						'value' => 10,
						'unit' => 'cm',
					],
					'style' => null,
					'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
				],
				'eyebrow' => [
					'care' => 5,
					'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
				],
				'eye' => [
					'left' => [
						'lenses' => false,
						'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
					],
					'right' => [
						'lenses' => false,
						'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
					],
				],
				'teeth' => ['care' => 10, 'braces' => true],
				'face' => [
					'care' => null,
					'freckles' => null,
					'shape' => null,
				],
				'hair' => [
					'style' => [
						'id' => 1,
						'name' => 'short',
					],
					'color' => ['name' => 'Black', 'hex' => '#000000', 'id' => 8],
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
					'ethnic_group' => ['id' => 1, 'name' => 'white'],
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
					'ethnic_group_id' => 1,
				],
				'hair' => [
					'style_id' => 1,
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
					'shape' => null,
				],
				'body' => [
					'build_id' => 1,
					'skin_color_id' => 8,
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