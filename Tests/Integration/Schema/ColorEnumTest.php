<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ColorEnumTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCustomFormatColors() {
		$colors = (new Schema\ColorEnum('hair_colors', $this->connection))->values();
		Assert::same(current($colors)['id'], key($colors));
		Assert::true(isset(current($colors)['id']));
		Assert::true(isset(current($colors)['name']));
		Assert::true(isset(current($colors)['hex']));
		Assert::true(count($colors) > 1);
	}
}

(new ColorEnumTest())->run();
