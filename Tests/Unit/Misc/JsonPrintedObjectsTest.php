<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class JsonPrintedObjectsTest extends Tester\TestCase {
	public function testMergingMultipleToPrettyArray() {
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

	public function testMergingSingleToPrettyArray() {
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

	public function testAdjustingWithKeptOrder() {
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
			))->adjusted(null, function (array $input): array {
				return array_map('strtoupper', $input);
			})->serialization()
		);
	}
}

(new JsonPrintedObjectsTest())->run();
