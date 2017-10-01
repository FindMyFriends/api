<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
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

final class OwnedDemands extends \Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testAskingForFirstDemand() {
		$demand = (new Domain\OwnedDemands(new Access\FakeUser('1'), $this->database))->ask(
			[
				'general' => [
					'age' => '[20,22)',
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
					'left_eye' => [
						'color' => null,
						'lenses' => null,
					],
					'right_eye' => [
						'color' => null,
						'lenses' => null,
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
		Assert::equal(new Domain\StoredDemand(1, $this->database), $demand);
		(new Misc\TableCount($this->database, 'demands', 1))->assert();
		(new Misc\TableCount($this->database, 'descriptions', 1))->assert();
		(new Misc\TableCount($this->database, 'bodies', 1))->assert();
		(new Misc\TableCount($this->database, 'faces', 1))->assert();
		(new Misc\TableCount($this->database, 'general', 1))->assert();
	}

	public function testAllForSpecifiedSeeker() {
		(new Misc\SampleDemand($this->database, ['seeker' => '1']))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => '2']))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => '3']))->try();
		(new Misc\SampleDemand($this->database, ['seeker' => '1']))->try();
		$demands = (new Domain\OwnedDemands(new Access\FakeUser('1'), $this->database))->all(new Dataset\FakeSelection('', []));
		$demand = $demands->current();
		Assert::contains('"seeker_id": 1', $demand->print(new Output\Json)->serialization());
		$demands->next();
		$demand = $demands->current();
		Assert::contains('"seeker_id": 1', $demand->print(new Output\Json)->serialization());
		$demands->next();
		Assert::null($demands->current());
	}
}

(new OwnedDemands())->run();