<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Schema;

use FindMyFriends\Schema;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class NestedPropertiesTest extends Tester\TestCase {
	public function testGatheredPropertiesObjects() {
		Assert::same(
			[
				'status',
				'outer.inner.nested',
				'size',
			],
			(new Schema\NestedProperties(
				new class implements Schema\Properties {
					public function objects(): array {
						return [
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
						];
					}
				}
			))->objects()
		);
	}
}

(new NestedPropertiesTest())->run();
