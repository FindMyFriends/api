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

final class StoredChange extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAffectingWholeForSpecificId() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution(
			$this->database,
			['evolved_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker' => $seeker]
		))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$evolution = new Evolution\StoredChange(1, $this->database);
		$evolution->affect(
			[
				'general' => [
					'birth_year' => [
						'from' => 1996,
						'to' => 1998,
					],
					'gender' => 'man',
					'race' => 'european',
					'firstname' => 'Dom',
					'lastname' => 'Klapuch',
				],
				'face' => [
					'teeth' => [
						'care' => 'high',
						'braces' => false,
					],
					'freckles' => false,
					'complexion' => 'medium',
					'beard' => 'no',
					'acne' => false,
					'shape' => 'oval',
					'hair' => [
						'style' => 'normal',
						'color' => 'black',
						'length' => 20,
						'highlights' => false,
						'roots' => true,
						'nature' => false,
					],
					'eyebrow' => 'black',
					'eye' => [
						'left' => [
							'color' => 'blue',
							'lenses' => false,
						],
						'right' => [
							'color' => 'blue',
							'lenses' => false,
						],
					],
				],
				'body' => [
					'build' => 'skinny',
					'skin' => 'white',
					'weight' => 60,
					'height' => 181,
				],
			]
		);
		Assert::equal(
			[
				'general' => [
					'race' => 'european',
					'gender' => 'man',
					'lastname' => 'Klapuch',
					'firstname' => 'Dom',
					'age' => ['from' => '19', 'to' => '21'],
				],
				'face' => [
					'teeth' => ['care' => 'high', 'braces' => false],
					'shape' => 'oval',
					'eye' => [
						'left' => ['color' => 'blue', 'lenses' => false],
						'right' => ['color' => 'blue', 'lenses' => false],
					],
					'hair' => [
						'color' => 'black',
						'roots' => true,
						'style' => 'normal',
						'length' => 20,
						'nature' => false,
						'highlights' => false,
					],
					'freckles' => false,
					'eyebrow' => 'black',
					'complexion' => 'medium',
					'beard' => 'no',
					'acne' => false,
				],
				'body' => [
					'height' => 181,
					'weight' => 60,
					'skin' => 'white',
					'build' => 'skinny',
				],
				'evolved_at' => '2017-09-16 00:00:00+00',
				'id' => 1,
			],
			json_decode($evolution->print(new Output\Json)->serialization(), true)
		);
	}

	public function testAffectingAllRelatedBirthYears() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution(
			$this->database,
			['evolved_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker' => $seeker]
		))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['general' => ['birth_year' => '[1990,1993)']]))->try();
		$evolution = new Evolution\StoredChange(1, $this->database);
		$evolution->affect(
			[
				'general' => [
					'birth_year' => [
						'from' => 1996,
						'to' => 1998,
					],
					'gender' => 'man',
					'race' => 'european',
					'firstname' => 'Dom',
					'lastname' => 'Klapuch',
				],
				'face' => [
					'teeth' => [
						'care' => 'high',
						'braces' => false,
					],
					'freckles' => false,
					'complexion' => 'medium',
					'beard' => 'no',
					'acne' => false,
					'shape' => 'oval',
					'hair' => [
						'style' => 'normal',
						'color' => 'black',
						'length' => 20,
						'highlights' => false,
						'roots' => true,
						'nature' => false,
					],
					'eyebrow' => 'black',
					'eye' => [
						'left' => [
							'color' => 'blue',
							'lenses' => false,
						],
						'right' => [
							'color' => 'blue',
							'lenses' => false,
						],
					],
				],
				'body' => [
					'build' => 'skinny',
					'skin' => 'white',
					'weight' => 60,
					'height' => 181,
				],
			]
		);
		Assert::same(
			[
				['birth_year' => '[1990,1993)'],
				['birth_year' => '[1996,1998)'],
				['birth_year' => '[1996,1998)'],
			],
			(new Storage\ParameterizedQuery(
				$this->database,
				'SELECT birth_year FROM general'
			))->rows()
		);
	}
}

(new StoredChange())->run();