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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IndividualEvolutions extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCopyingBirthYearFromAncestor() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$change = (new Domain\IndividualEvolutions(
			new Access\FakeUser((string) $seeker),
			$this->database
		))->evolve(
			[
				'evolved_at' => '2017-01-01',
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
		Assert::contains('"birth_year": "\"to\"=>\"2000\", \"from\"=>\"1999\""', $change->print(new Output\Json())->serialization());
		(new Misc\TableCount($this->database, 'evolutions', 7))->assert();
		(new Misc\TableCount($this->database, 'descriptions', 7))->assert();
		(new Misc\TableCount($this->database, 'bodies', 7))->assert();
		(new Misc\TableCount($this->database, 'faces', 7))->assert();
		(new Misc\TableCount($this->database, 'general', 7))->assert();
	}

	public function testCountingBySeeker() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		Assert::same(
			2,
			(new Domain\IndividualEvolutions(
				new Access\FakeUser((string) $seeker),
				$this->database
			))->count(new Dataset\EmptySelection())
		);
	}

	public function testEvolutionChainBySeeker() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker, 'general' => ['gender' => 'man', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker, 'general' => ['gender' => 'woman', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		$chain = (new Domain\IndividualEvolutions(
			new Access\FakeUser((string) $seeker),
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