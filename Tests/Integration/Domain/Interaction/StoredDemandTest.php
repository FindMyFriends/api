<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Interaction;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class StoredDemandTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRemovingSingleDemand() {
		(new Misc\SampleDemand($this->connection))->try();
		(new Misc\SampleDemand($this->connection))->try();
		(new Interaction\StoredDemand(1, $this->connection))->retract();
		(new Misc\TableCount($this->connection, 'demands', 1))->assert();
		Assert::same(
			2,
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT id FROM demands'
			))->field()
		);
	}

	public function testReconsideringAsWholeForSpecificId() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleDemand(
			$this->connection,
			['created_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleDemand($this->connection))->try();
		$demand = new Interaction\StoredDemand(1, $this->connection);
		$demand->reconsider(
			[
				'note' => null,
				'general' => [
					'age' => [
						'from' => 19,
						'to' => 21,
					],
					'firstname' => null,
					'lastname' => null,
					'sex' => 'man',
					'ethnic_group_id' => 1,
				],
				'hair' => [
					'style_id' => 1,
					'color_id' => 8,
					'length_id' => 1,
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'beard' => [
					'color_id' => 8,
					'length_id' => 1,
					'style_id' => null,
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
				'eyebrow' => [
					'color_id' => 8,
					'care' => 5,
				],
				'face' => [
					'care' => null,
					'freckles' => null,
					'shape_id' => 1,
				],
				'body' => [
					'build_id' => 1,
					'breast_size' => 'B',
				],
				'hands' => [
					'nails' => [
						'length_id' => 1,
						'color_id' => 8,
					],
					'visible_veins' => null,
					'care' => null,
				],
			]
		);
		Assert::equal(
			[
				'note' => null,
				'hands' => [
					'nails' => [
						'length_id' => 1,
						'color_id' => 8,
					],
					'visible_veins' => null,
					'care' => null,
				],
				'body' => [
					'build_id' => 1,
					'breast_size' => 'B',
				],
				'beard' => [
					'length_id' => 1,
					'style_id' => null,
					'color_id' => 8,
				],
				'eyebrow' => [
					'care' => 5,
					'color_id' => 8,
				],
				'eye' => [
					'left' => [
						'lenses' => false,
						'color_id' => 8,
					],
					'right' => [
						'lenses' => false,
						'color_id' => 8,
					],
				],
				'teeth' => ['care' => 10, 'braces' => true],
				'face' => [
					'care' => null,
					'freckles' => null,
					'shape_id' => 1,
				],
				'hair' => [
					'style_id' => 1,
					'color_id' => 8,
					'length_id' => 1,
					'highlights' => null,
					'roots' => null,
					'nature' => null,
				],
				'general' => [
					'age' => ['to' => 21, 'from' => 19],
					'firstname' => null,
					'lastname' => null,
					'sex' => 'man',
					'ethnic_group_id' => 1,
				],
				'created_at' => '2017-09-16 00:00:00+00',
				'seeker_id' => $seeker,
				'id' => 1,
			],
			json_decode($demand->print(new Output\Json())->serialization(), true)
		);
	}

	public function testReconsideringOnlyPart() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleDemand(
			$this->connection,
			['created_at' => new \DateTime('2017-09-16 00:00:00+00'), 'seeker_id' => $seeker]
		))->try();
		(new Misc\SampleDemand($this->connection))->try();
		$demand = new Interaction\StoredDemand(1, $this->connection);
		$demand->reconsider(['note' => 'new note']);
		['note' => $note] = json_decode($demand->print(new Output\Json())->serialization(), true);
		Assert::same('new note', $note);
	}
}

(new StoredDemandTest())->run();
