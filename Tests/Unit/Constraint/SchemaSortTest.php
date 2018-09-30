<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class SchemaSortTest extends TestCase\Runtime {
	public function testPassingOnAllSortsInSchema(): void {
		Assert::same(
			['sort' => ['status' => 'ASC', 'size' => 'DESC']],
			(new Constraint\SchemaSort(
				new class extends Dataset\Sort {
					protected function sort(): array {
						return ['status' => 'ASC', 'size' => 'DESC'];
					}
				},
				new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
			))->criteria()
		);
	}

	/**
	 * @throws \UnexpectedValueException Following criteria are not allowed: "foo"
	 */
	public function testThrowingOnUnknownSortProperty(): void {
		(new Constraint\SchemaSort(
			new class extends Dataset\Sort {
				protected function sort(): array {
					return ['foo' => 'ASC', 'size' => 'DESC'];
				}
			},
			new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
		))->criteria();
	}

	/**
	 * @throws \UnexpectedValueException Following criteria are not allowed: "status"
	 */
	public function testThrowingOnForbiddenProperties(): void {
		(new Constraint\SchemaSort(
			new class extends Dataset\Sort {
				protected function sort(): array {
					return ['status' => 'ASC', 'size' => 'DESC'];
				}
			},
			new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json')),
			['status']
		))->criteria();
	}

	public function testPassingOnNestedObject(): void {
		Assert::same(
			['sort' => ['outer.inner.nested' => 'ASC']],
			(new Constraint\SchemaSort(
				new class extends Dataset\Sort {
					protected function sort(): array {
						return ['outer.inner.nested' => 'ASC'];
					}
				},
				new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
			))->criteria()
		);
	}

	private function testingSchema(): string {
		return (string) json_encode(
			[
				'$schema' => 'http://json-schema.org/draft-04/schema#',
				'additionalProperties' => false,
				'properties' => [
					'status' => [
						'type' => ['string'],
						'enum' => ['success', 'fail'],
					],
					'outer' => [
						'properties' => [
							'inner' => [
								'properties' => [
									'nested' => [
										'type' => 'string',
									],
								],
							],
						],
					],
					'size' => [
						'type' => ['integer'],
						'enum' => [1, 2, 3],
					],
				],
				'type' => 'object',
			]
		);
	}
}

(new SchemaSortTest())->run();
