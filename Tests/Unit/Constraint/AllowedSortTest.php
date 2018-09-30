<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class AllowedSortTest extends TestCase\Runtime {
	/**
	 * @throws \UnexpectedValueException Following sorts are not allowed: "status"
	 */
	public function testThrowingOnNotAllowedSort(): void {
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
	public function testIgnoringCases(): void {
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
	public function testThrowingOnNotAllowedMultipleSorts(): void {
		(new Constraint\AllowedSort(
			new class extends Dataset\Sort {
				protected function sort(): array {
					return ['status' => 'ASC', 'size' => 'DESC', 'okey' => 'bar'];
				}
			},
			['okey']
		))->criteria();
	}

	public function testPassingOnEverythingAllowed(): void {
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
