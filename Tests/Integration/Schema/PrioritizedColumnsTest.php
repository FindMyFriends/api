<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Schema;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class PrioritizedColumnsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPriorityBySeeker() {
		['id' => $me] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $foreign] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $me, 'firstname' => 'Dom']))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $me, 'firstname' => 'Dominik']))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $me, 'firstname' => 'FooBar']))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $foreign]))->try();
		(new Storage\NativeQuery($this->database, 'REFRESH MATERIALIZED VIEW prioritized_evolution_fields'))->execute();
		$columns = (new Schema\Evolution\PrioritizedColumns($this->database, new Access\FakeSeeker((string) $me)))->values();
		Assert::count(3, $columns);
		Assert::same(3, current($columns));
	}

	public function testAddingAnyColumnsForSeekerWithoutRefresh() {
		['id' => $me] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $me, 'firstname' => 'Dom']))->try();
		$columns = (new Schema\Evolution\PrioritizedColumns($this->database, new Access\FakeSeeker((string) $me)))->values();
		Assert::count(3, $columns);
		Assert::same(1, $columns['general.sex']);
		Assert::same(2, $columns['general.firstname']);
		Assert::same(3, $columns['general.lastname']);
	}
}

(new PrioritizedColumnsTest())->run();
