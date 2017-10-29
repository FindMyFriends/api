<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1\Evolutions;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1']))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => '1']))->try();
		$demands = json_decode(
			(new V1\Evolutions\Get(
				new Uri\FakeUri('/', 'v1/evolutions', []),
				$this->database,
				new Access\FakeUser('1', ['role' => 'member'])
			))->template(['page' => 1, 'per_page' => 10])->render()
		);
		Assert::count(2, $demands);
		Assert::notSame([], $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Evolutions/schema/get.json')
		))->assert();
	}
}

(new Get())->run();