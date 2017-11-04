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

final class IndividualDemands extends \Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		$demand = (new Domain\IndividualDemands(new Access\FakeUser((string) $seeker), $this->database))->ask(
			[
				'general' => [
					'birth_year' => '[1996,1998)',
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
				'location' => [
					'coordinates' => ['latitude' => 50.15, 'longitude' => 14.2],
					'met_at' => '[2017-01-01,2017-01-02)',
				],
			]
		);
		Assert::equal(new Domain\StoredDemand(1, $this->database), $demand);
		(new Misc\TableCount($this->database, 'demands', 1))->assert();
		(new Misc\TableCount($this->database, 'descriptions', 1))->assert();
		(new Misc\TableCount($this->database, 'bodies', 1))->assert();
		(new Misc\TableCount($this->database, 'faces', 1))->assert();
		(new Misc\TableCount($this->database, 'general', 1))->assert();
	}

	public function testAllForSpecifiedSeeker() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => $seeker]))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => $seeker]))->try();
		$demands = (new Domain\IndividualDemands(
			new Access\FakeUser((string) $seeker),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json)->serialization());
		$demands->next();
		$demand = $demands->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $demand->print(new Output\Json)->serialization());
		$demands->next();
		Assert::null($demands->current());
	}

	public function testCounting() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => $seeker]))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => $seeker]))->try();
		Assert::same(
			2,
			(new Domain\IndividualDemands(
				new Access\FakeUser((string) $seeker),
				$this->database
			))->count(new Dataset\FakeSelection(null, []))
		);
	}
}

(new IndividualDemands())->run();