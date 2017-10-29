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
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredEvolution extends \Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPrinting() {
		(new Misc\SampleEvolutions(
			$this->database,
			[
				'evolved_at' => new \DateTime('2017-09-16 00:00:00+00'),
				'general' => [
					'birth_year' => '[1996,1998)',
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
					'birth_year' => '[1996,1998)',
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
				'id' => 1,
				'evolved_at' => '2017-09-16 00:00:00+00',
			],
			json_decode(
				(new Domain\StoredEvolution(1, $this->database))->print(new Output\Json)->serialization(),
				true
			)
		);
	}
}

(new StoredEvolution())->run();