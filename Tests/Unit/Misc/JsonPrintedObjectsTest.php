<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class JsonPrintedObjectsTest extends TestCase\Runtime {
	public function testMergingMultipleToPrettyArray(): void {
		Assert::same(
			'[
    {
        "a": "b"
    },
    {
        "c": "d"
    }
]',
			(new Misc\JsonPrintedObjects(
				new class {
					public function print(): Output\Format {
						return new Output\Json(['a' => 'b']);
					}
				},
				new class {
					public function print(): Output\Format {
						return new Output\Json(['c' => 'd']);
					}
				}
			))->serialization()
		);
	}

	public function testMergingSingleToPrettyArray(): void {
		Assert::same(
			'[
    {
        "a": "b"
    }
]',
			(new Misc\JsonPrintedObjects(
				new class {
					public function print(): Output\Format {
						return new Output\Json(['a' => 'b']);
					}
				}
			))->serialization()
		);
	}

	public function testAdjustingWithKeptOrder(): void {
		Assert::same(
			'[
    {
        "a": "B"
    },
    {
        "c": "D"
    }
]',
			(new Misc\JsonPrintedObjects(
				new class {
					public function print(): Output\Format {
						return new Output\Json(['a' => 'b']);
					}
				},
				new class {
					public function print(): Output\Format {
						return new Output\Json(['c' => 'd']);
					}
				}
			))->adjusted(null, static function (array $input): array {
				return array_map('strtoupper', $input);
			})->serialization()
		);
	}
}

(new JsonPrintedObjectsTest())->run();
