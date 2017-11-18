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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker' => $seeker]))->try();
		$demands = json_decode(
			(new V1\Evolutions\Get(
				new Uri\FakeUri('/', 'v1/evolutions', []),
				$this->database,
				new Access\FakeUser((string) $seeker, ['role' => 'member'])
			))->template(['page' => 1, 'per_page' => 10])->render()
		);
		Assert::count(2, $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Evolution/schema/get.json')
		))->assert();
	}
}

(new GetTest())->run();