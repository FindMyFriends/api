<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class JsonPropertiesTest extends TestCase\Runtime {
	public function testGatheredPropertiesObjects(): void {
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
		return (string) json_encode(
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
	public function testThrowingOnUnknownFile(): void {
		(new Schema\JsonProperties(new \SplFileInfo('foo')))->objects();
	}
}

(new JsonPropertiesTest())->run();
