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
use Klapuch\Access;
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
		$change = (new Evolution\IndividualChain(
			new Access\FakeUser((string) $seeker),
			$this->database
		))->extend(
			[
				'evolved_at' => '2015-01-01',
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
				'hands' => [
					'nails' => [
						'length' => null,
						'care' => null,
						'color' => null,
					],
					'veins' => null,
					'joint' => null,
					'care' => null,
					'hair' => null,
				],
			]
		);
		$change = json_decode($change->print(new Output\Json())->serialization(), true);
		Assert::same('15', $change['general']['age']['from']);
		Assert::same('16', $change['general']['age']['to']);
		(new Misc\TableCounts(
			$this->database,
			[
				'evolutions' => 7,
				'descriptions' => 7,
				'bodies' => 7,
				'faces' => 7,
				'general' => 7,
				'seekers' => 5,
				'hands' => 7,
			]
		))->assert();
	}

	public function testCountingBySeeker() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		Assert::same(
			2,
			(new Evolution\IndividualChain(
				new Access\FakeUser((string) $seeker),
				$this->database
			))->count(new Dataset\EmptySelection())
		);
	}

	public function testChainBySeeker() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['gender' => 'man', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker, 'general' => ['gender' => 'woman', 'birth_year' => '[1999,2000)']]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker2]))->try();
		$chain = (new Evolution\IndividualChain(
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

(new IndividualChainTest())->run();