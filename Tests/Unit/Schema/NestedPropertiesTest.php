<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class NestedPropertiesTest extends TestCase\Runtime {
	public function testGatheredPropertiesObjects(): void {
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
