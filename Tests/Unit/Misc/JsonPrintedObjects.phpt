<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class JsonPrintedObjects extends \Tester\TestCase {
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
}

(new JsonPrintedObjects())->run();