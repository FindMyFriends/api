<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class StoredChangeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAffectingWholeForSpecificId() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution(
			$this->database,
			['evolved_at' => new \DateTime('2018-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$evolution = new Evolution\StoredChange(1, $this->database);
		$evolution->affect(
			[
				'evolved_at' => '2018-09-16 00:00:00+00',
				'general' => [
					'firstname' => null,
					'lastname' => null,
					'sex' => 'man',
					'ethnic_group_id' => 1,
					'age' => 20,
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
					'shape_id' => 1,
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
		Assert::equal(
			[
				'hands' => [
					'nails' => [
						'length_id' => 1,
						'color_id' => 8,
					],
					'visible_veins' => null,
					'care' => null,
				],
				'body' => [
					'build_id' => 1,
					'breast_size' => 'B',
				],
				'beard' => [
					'length_id' => 1,
					'style_id' => null,
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
					'length_id' => 1,
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'general' => [
					'age' => 20,
					'firstname' => null,
					'lastname' => null,
					'sex' => 'man',
					'ethnic_group_id' => 1,
				],
				'evolved_at' => '2018-09-16 00:00:00+00',
				'id' => 1,
				'seeker_id' => $seeker,
			],
			json_decode($evolution->print(new Output\Json())->serialization(), true)
		);
	}

	public function testAffectingAllRelatedBirthYears() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution(
			$this->database,
			['evolved_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['general' => ['birth_year' => 1990]]))->try();
		$evolution = new Evolution\StoredChange(1, $this->database);
		$evolution->affect(
			[
				'evolved_at' => '2017-09-16 00:00:00+00',
				'general' => [
					'age' => 20,
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
					'shape_id' => 1,
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
				],
			]
		);
		Assert::same(
			[
				['birth_year' => 1990],
				['birth_year' => 1998],
				['birth_year' => 1998],
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
