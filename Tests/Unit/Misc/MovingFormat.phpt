<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class MovingFormat extends \Tester\TestCase {
	public function testMovingByKeys() {
		Assert::same(
			'<root><general><age>21</age><firstname>Dom</firstname><hair><color>blue</color></hair></general><id>1</id><seeker_id>2</seeker_id></root>',
			(new Misc\MovingFormat(
				new Output\Xml([], 'root'),
				['age' => 21, 'firstname' => 'Dom', 'id' => 1, 'seeker_id' => 2, 'color' => 'blue'],
				['general' => ['age', 'firstname', 'hair' => ['color']], 'id', 'seeker_id']
			))->serialization()
		);
	}
}

(new MovingFormat())->run();