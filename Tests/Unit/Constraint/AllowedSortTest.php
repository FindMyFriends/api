<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Klapuch\Dataset;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class AllowedSortTest extends Tester\TestCase {
	/**
	 * @throws \UnexpectedValueException Following sorts are not allowed: "status"
	 */
	public function testThrowingOnNotAllowedSort() {
		(new Constraint\AllowedSort(
			new class extends Dataset\Sort {
				protected function sort(): array {
					return ['status' => 'ASC', 'size' => 'DESC'];
				}
			},
			['size']
		))->criteria();
	}

	/**
	 * @throws \UnexpectedValueException Following sorts are not allowed: "foo"
	 */
	public function testIgnoringCases() {
		(new Constraint\AllowedSort(
			new class extends Dataset\Sort {
				protected function sort(): array {
					return ['status' => 'ASC', 'SIZE' => 'DESC', 'foo' => 'DESC'];
				}
			},
			['size', 'STATUS']
		))->criteria();
	}

	/**
	 * @throws \UnexpectedValueException Following sorts are not allowed: "status, size"
	 */
	public function testThrowingOnNotAllowedMultipleSorts() {
		(new Constraint\AllowedSort(
			new class extends Dataset\Sort {
				protected function sort(): array {
					return ['status' => 'ASC', 'size' => 'DESC', 'okey' => 'bar'];
				}
			},
			['okey']
		))->criteria();
	}

	public function testPassingOnEverythingAllowed() {
		Assert::same(
			['sort' => ['status' => 'ASC', 'size' => 'DESC']],
			(new Constraint\AllowedSort(
				new class extends Dataset\Sort {
					protected function sort(): array {
						return ['status' => 'ASC', 'size' => 'DESC'];
					}
				},
				['size', 'status']
			))->criteria()
		);
	}
}

(new AllowedSortTest())->run();
