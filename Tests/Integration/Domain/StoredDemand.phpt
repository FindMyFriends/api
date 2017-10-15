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
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredDemand extends \Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRemovingSingleDemand() {
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Domain\StoredDemand(1, $this->database))->retract();
		(new Misc\TableCount($this->database, 'demands', 1))->assert();
		Assert::same(
			2,
			(new Storage\ParameterizedQuery(
				$this->database,
				'SELECT id FROM demands'
			))->field()
		);
	}

	public function testPrinting() {
		(new Misc\SampleDemand(
			$this->database,
			[
				'seeker' => 1,
				'created_at' => new \DateTime('2017-09-16 00:00:00+00'),
				'general' => [
					'age' => '[20,22)',
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
					'left_eye' => [
						'color' => 'blue',
						'lenses' => false,
					],
					'right_eye' => [
						'color' => 'blue',
						'lenses' => false,
					],
				],
				'body' => [
					'build' => 'skinny',
					'skin' => 'white',
					'weight' => 60,
					'height' => 181,
				],
			]
		))->try();
		Assert::equal(
			[
				'general' => [
					'race' => 'european',
					'gender' => 'man',
					'lastname' => 'Klapuch',
					'firstname' => 'Dom',
					'age' => '[20,22)',
				],
				'face' => [
					'teeth' => ['care' => 'high', 'braces' => false],
					'shape' => 'oval',
					'eye' => [
						'right' => ['color' => 'blue', 'lenses' => false],
						'left' => ['color' => 'blue', 'lenses' => false],
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
				'created_at' => '2017-09-16 00:00:00+00',
				'seeker_id' => 1,
				'id' => 1,
			],
			json_decode(
				(new Domain\StoredDemand(1, $this->database))->print(new Output\Json)->serialization(),
				true
			)
		);
	}

	public function testReconsideringAsWholeForSpecificId() {
		(new Misc\SampleDemand(
			$this->database,
			['created_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker' => 1]
		))->try();
		(new Misc\SampleDemand($this->database))->try();
		$demand = new Domain\StoredDemand(1, $this->database);
		$demand->reconsider(
			[
				'general' => [
					'age' => '[20,22)',
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
					'age' => '[20,22)',
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
				'created_at' => '2017-09-16 00:00:00+00',
				'seeker_id' => 1,
				'id' => 1,
			],
			json_decode($demand->print(new Output\Json)->serialization(), true)
		);
		$print = json_decode((new Domain\StoredDemand(2, $this->database))->print(new Output\Json())->serialization(), true);
		Assert::notContains('Dom', $print);
		Assert::notContains('black', $print);
		Assert::notContains('skinny', $print);
	}
}

(new StoredDemand())->run();