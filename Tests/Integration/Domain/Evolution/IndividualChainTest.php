<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class IndividualChainTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCopyingBirthYearFromAncestor() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$changeId = (new Evolution\IndividualChain(
			new Access\FakeSeeker((string) $seeker),
			$this->database
		))->extend(
			[
				'evolved_at' => '2015-01-01',
				'general' => [
					'firstname' => null,
					'lastname' => null,
					'sex' => 'man',
					'ethnic_group_id' => 1,
				],
				'hair' => [
					'style_id' => 1,
					'color_id' => 8,
					'length' => [
						'value' => 1,
						'unit' => 'mm',
					],
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'beard' => [
					'color_id' => 8,
					'length' => [
						'value' => 5,
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
							'value' => 1,
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
		Assert::same(7, $changeId);
		(new Misc\TableCounts(
			$this->database,
			[
				'evolutions' => 7,
				'descriptions' => 7,
				'bodies' => 7,
				'faces' => 7,
				'general' => 7,
				'eyes' => 14,
				'hair' => 7,
				'nails' => 7,
				'teeth' => 7,
				'eyebrows' => 7,
				'hand_hair' => 7,
				'beards' => 7,
				'seekers' => 5,
				'hands' => 7,
			]
		))->assert();
	}

	public function testCountingBySeeker() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		Assert::same(
			2,
			(new Evolution\IndividualChain(
				new Access\FakeSeeker((string) $seeker),
				$this->database
			))->count(new Dataset\EmptySelection())
		);
	}

	public function testChainBySeeker() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['sex' => 'man', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['sex' => 'woman', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$chain = (new Evolution\IndividualChain(
			new Access\FakeSeeker((string) $seeker),
			$this->database
		))->changes(new Dataset\EmptySelection());
		Assert::contains('"sex": "man"', $chain->current()->print(new Output\Json())->serialization());
		$chain->next();
		Assert::contains('"sex": "woman"', $chain->current()->print(new Output\Json())->serialization());
		$chain->next();
		Assert::null($chain->current());
	}
}

(new IndividualChainTest())->run();
