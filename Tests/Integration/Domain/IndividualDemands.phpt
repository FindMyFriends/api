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

final class IndividualDemands extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		$demand = (new Domain\IndividualDemands(new Access\FakeUser((string) $seeker), $this->database))->ask(
			[
				'general' => [
					'birth_year' => [
						'from' => 1996,
						'to' => 1998,
					],
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
					'met_at' => [
						'from' => '2017-01-01',
						'to' => '2017-01-02',
					],
				],
			]
		);
		Assert::equal(new Domain\StoredDemand(1, $this->database), $demand);
		(new Misc\TableCounts(
			$this->database,
			[
				'seekers' => 1,
				'locations' => 1,
				'descriptions' => 1,
				'demands' => 1,
				'bodies' => 1,
				'general' => 1,
				'faces' => 1,
			]
		))->assert();
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