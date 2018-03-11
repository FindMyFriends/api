<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CreatedResourceUrlTest extends Tester\TestCase {
	public function testPlaceholderReplacedByArrayKeyValue() {
		Assert::same(
			'demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}'),
				['id' => 5]
			))->path()
		);
	}

	public function testStrippingLeadingSlash() {
		Assert::same(
			'demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, '/demands/{id}'),
				['id' => 5]
			))->path()
		);
	}

	public function testKeepingTrailingSlash() {
		Assert::same(
			'demands/5/',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}/'),
				['id' => 5]
			))->path()
		);
	}

	public function testInjectingMultipleSameParametersWithSameValue() {
		Assert::same(
			'demands/5/foo/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}/foo/{id}'),
				['id' => 5]
			))->path()
		);
	}

	public function testInjectingMultipleDifferentParameters() {
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
	public function testThrowingOnMissingPlaceholder() {
		(new Http\CreatedResourceUrl(
			new FakeUri(null, 'demands/{id}/foo/{name}'),
			['id' => 5]
		))->path();
	}

	/**
	 * @throws \UnexpectedValueException Placeholders "id, name" are unused
	 */
	public function testThrowingOnMultipleMissedPlaceholders() {
		(new Http\CreatedResourceUrl(
			new FakeUri(null, 'demands/{id}/foo/{name}'),
			[]
		))->path();
	}

	public function testPassingWithNotAllUsedParameters() {
		Assert::same(
			'demands/5',
			(new Http\CreatedResourceUrl(
				new FakeUri(null, 'demands/{id}'),
				['id' => 5, 'name' => 'bar']
			))->path()
		);
	}

	public function testMergedReferenceWithPath() {
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