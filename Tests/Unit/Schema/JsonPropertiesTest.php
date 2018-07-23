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

final class JsonPropertiesTest extends Tester\TestCase {
	public function testGatheredPropertiesObjects() {
		Assert::same(
			[
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
			],
			(new Schema\JsonProperties(
				new \SplFileInfo(Tester\FileMock::create($this->testingSchema(), 'json'))
			))->objects()
		);
	}

	private function testingSchema(): string {
		return json_encode(
			[
				'$schema' => 'http://json-schema.org/draft-04/schema#',
				'additionalProperties' => false,
				'properties' => [
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
				],
				'type' => 'object',
			]
		);
	}

	/**
	 * @throws \UnexpectedValueException Schema can not be loaded
	 */
	public function testThrowingOnUnknownFile() {
		(new Schema\JsonProperties(new \SplFileInfo('foo')))->objects();
	}
}

(new JsonPropertiesTest())->run();
