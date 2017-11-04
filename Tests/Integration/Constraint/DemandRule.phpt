<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DemandRule extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testApplicationWithAllReturnedValues() {
		Assert::same(
			[
				'general' => [
					'birth_year' => '[1996,1997]',
				],
				'location' => [
					'met_at' => '["2017-01-01","2017-01-02")',
				],
			],
			(new Constraint\DemandRule(
				$this->database
			))->apply(
				[
					'general' => ['birth_year' => '[1996,1997]'],
					'location' => [
						'met_at' => '["2017-01-01","2017-01-02")',
					],
				]
			)
		);
	}
}

(new DemandRule())->run();