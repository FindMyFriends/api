<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1\Schema;

use JsonSchema;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Test extends Tester\TestCase {
	/**
	 * @dataProvider expectations.ini
	 */
	public function testSchemas(string $schema, string $master, string $replacements, bool $valid) {
		$json = json_decode(
			json_encode(
				array_replace_recursive(
					json_decode(file_get_contents((new \SplFileInfo($master))->getPathname()), true),
					json_decode(file_get_contents((new \SplFileInfo($replacements))->getPathname()), true)
				)
			)
		);
		$validator = new JsonSchema\Validator();
		$validator->validate($json, ['$ref' => 'file://' . (new \SplFileInfo($schema))->getRealPath()]);
		Assert::same(
			$valid,
			$validator->isValid(),
			sprintf('%s: %s', current($validator->getErrors())['message'], current($validator->getErrors())['property'])
		);
	}
}

(new Test())->run();