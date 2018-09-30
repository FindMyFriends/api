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
final class SchemaFilterTest extends TestCase\Runtime {
	public function testPassingOnAllValuesInEnum(): void {
		Assert::same(
			['filter' => ['status' => 'success', 'size' => 2]],
			(new Constraint\SchemaFilter(
				new class extends Dataset\Filter {
					protected function filter(): array {
						return ['status' => 'success', 'size' => 2];
					}
				},
				new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
			))->criteria()
		);
	}

	/**
	 * @throws \UnexpectedValueException Schema "foo.txt" is not readable
	 */
	public function testThrowingOnNotReadableFile(): void {
		(new Constraint\SchemaFilter(
			new class extends Dataset\Filter {
				protected function filter(): array {
					return [];
				}
			},
			new \SplFileInfo('foo.txt')
		))->criteria();
	}

	/**
	 * @throws \UnexpectedValueException 'status' must be one of: 'success', 'fail' - 'foo' was given
	 */
	public function testFailingOnAnyValueOutOfEnum(): void {
		(new Constraint\SchemaFilter(
			new class extends Dataset\Filter {
				protected function filter(): array {
					return ['size' => 2, 'status' => 'foo'];
				}
			},
			new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
		))->criteria();
	}

	public function testNoRestPropertiesOutOfProperties(): void {
		Assert::same(
			['filter' => ['status' => 'success']],
			(new Constraint\SchemaFilter(
				new class extends Dataset\Filter {
					protected function filter(): array {
						return ['status' => 'success', 'bar' => 'baz'];
					}
				},
				new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
			))->criteria()
		);
	}

	/**
	 * @throws \UnexpectedValueException Following criteria are not allowed: "status"
	 */
	public function testThrowingOnForbidden(): void {
		(new Constraint\SchemaFilter(
			new class extends Dataset\Filter {
				protected function filter(): array {
					return ['status' => 'success', 'bar' => 'baz'];
				}
			},
			new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json')),
			['status']
		))->criteria();
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

(new SchemaFilterTest())->run();
