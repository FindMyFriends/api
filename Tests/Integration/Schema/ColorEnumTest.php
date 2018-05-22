<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ColorEnumTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCustomFormatColors() {
		$colors = (new Schema\ColorEnum('hair_colors', $this->database))->values();
		Assert::type('int', key($colors));
		Assert::true(isset(current($colors)['name']));
		Assert::true(isset(current($colors)['hex']));
		Assert::true(count($colors) > 1);
	}
}

(new ColorEnumTest())->run();
