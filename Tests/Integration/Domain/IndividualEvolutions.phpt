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
use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IndividualEvolutions extends \Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCopyingBirthYearFromAncestor() {
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1', 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1', 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$change = (new Domain\IndividualEvolutions(
			new Access\FakeUser('1'),
			$this->database
		))->evolve(
			[
				'general' => [
					'gender' => 'man',
					'race' => 'european',
					'firstname' => null,
					'lastname' => null,
				],
				'face' => [
					'teeth' => [
						'care' => null,
						'braces' => null,
					],
					'freckles' => false,
					'complexion' => null,
					'beard' => null,
					'acne' => false,
					'shape' => null,
					'hair' => [
						'style' => null,
						'color' => null,
						'length' => null,
						'highlights' => null,
						'roots' => null,
						'nature' => null,
					],
					'eyebrow' => null,
					'eye' => [
						'left' => [
							'color' => null,
							'lenses' => null,
						],
						'right' => [
							'color' => null,
							'lenses' => null,
						],
					],
				],
				'body' => [
					'build' => null,
					'skin' => null,
					'weight' => null,
					'height' => null,
				],
			]
		);
		Assert::contains('"birth_year": "[1999,2000)"', $change->print(new Output\Json())->serialization());
		(new Misc\TableCount($this->database, 'evolutions', 7))->assert();
		(new Misc\TableCount($this->database, 'descriptions', 7))->assert();
		(new Misc\TableCount($this->database, 'bodies', 7))->assert();
		(new Misc\TableCount($this->database, 'faces', 7))->assert();
		(new Misc\TableCount($this->database, 'general', 7))->assert();
	}

	public function testCountingBySeeker() {
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1', 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1', 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		Assert::same(
			2,
			(new Domain\IndividualEvolutions(
				new Access\FakeUser('1'),
				$this->database
			))->count(new Dataset\EmptySelection())
		);
	}

	public function testEvolutionChainBySeeker() {
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1', 'general' => ['gender' => 'man', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1', 'general' => ['gender' => 'woman', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$chain = (new Domain\IndividualEvolutions(
			new Access\FakeUser('1'),
			$this->database
		))->changes(new Dataset\EmptySelection());
		Assert::contains('"gender": "man"', $chain->current()->print(new Output\Json())->serialization());
		$chain->next();
		Assert::contains('"gender": "woman"', $chain->current()->print(new Output\Json())->serialization());
		$chain->next();
		Assert::null($chain->current());
	}
}

(new IndividualEvolutions())->run();