<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Domain;

use FindMyFriends\Domain;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class FormattedEvolution extends \Tester\TestCase {
	public function testPrintingDateTimeInIso8601() {
		Assert::same(
			'2017-09-17T13:58:10+00:00',
			(new Domain\FormattedEvolution(
				new Domain\FakeEvolution()
			))->print(new Output\ArrayFormat(['evolved_at' => '2017-09-17 13:58:10.531097+00']))->serialization(),
			true
		);
	}
}

(new FormattedEvolution())->run();