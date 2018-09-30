<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use FindMyFriends\TestCase;
use Klapuch\Uri\FakeUri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CreatedResourceUrlTest extends TestCase\Runtime {
	public function testPlaceholderReplacedByArrayKeyValue(): void {
		Assert::same(
			'demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}'),
				['id' => 5]
			))->path()
		);
	}

	public function testStrippingLeadingSlash(): void {
		Assert::same(
			'demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, '/demands/{id}'),
				['id' => 5]
			))->path()
		);
	}

	public function testKeepingTrailingSlash(): void {
		Assert::same(
			'demands/5/',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}/'),
				['id' => 5]
			))->path()
		);
	}

	public function testInjectingMultipleSameParametersWithSameValue(): void {
		Assert::same(
			'demands/5/foo/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}/foo/{id}'),
				['id' => 5]
			))->path()
		);
	}

	public function testInjectingMultipleDifferentParameters(): void {
		Assert::same(
			'demands/5/foo/bar',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}/foo/{name}'),
				['id' => 5, 'name' => 'bar']
			))->path()
		);
	}

	/**
	 * @throws \UnexpectedValueException Placeholder "name" is unused
	 */
	public function testThrowingOnMissingPlaceholder(): void {
		(new Http\CreatedResourceUrl(
			new FakeUri(null, 'demands/{id}/foo/{name}'),
			['id' => 5]
		))->path();
	}

	/**
	 * @throws \UnexpectedValueException Placeholders "id, name" are unused
	 */
	public function testThrowingOnMultipleMissedPlaceholders(): void {
		(new Http\CreatedResourceUrl(
			new FakeUri(null, 'demands/{id}/foo/{name}'),
			[]
		))->path();
	}

	public function testPassingWithNotAllUsedParameters(): void {
		Assert::same(
			'demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}'),
				['id' => 5, 'name' => 'bar']
			))->path()
		);
	}

	public function testMergedReferenceWithPath(): void {
		Assert::same(
			'http://localhost/demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri('http://localhost/', 'demands/{id}'),
				['id' => 5]
			))->reference()
		);
	}
}

(new CreatedResourceUrlTest())->run();
