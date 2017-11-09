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
					'birth_year' => [
						'from' => 1996,
						'to' => 1996,
					],
				],
			],
			(new Constraint\DemandRule(
				$this->database
			))->apply(
				[
					'general' => [
						'birth_year' => [
							'from' => 1996,
							'to' => 1996,
						],
					],
				]
			)
		);
	}
}

(new DemandRule())->run();