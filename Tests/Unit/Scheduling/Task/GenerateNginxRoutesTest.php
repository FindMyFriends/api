<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Scheduling\Task;

use FindMyFriends\Scheduling;
use FindMyFriends\TestCase;
use Klapuch\Configuration;
use Tester\Assert;
use Tester\FileMock;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class GenerateNginxRoutesTest extends TestCase\Runtime {
	public function testReplacingAllPlaceholders(): void {
		$destination = FileMock::create();
		(new Scheduling\Task\GenerateNginxRoutes(
			new Configuration\FakeSource([
				'demands/{id}' => [
					'location' => '~ ^/demands/{id}$',
					'methods' => ['GET'],
					'params' => ['id' => '[a-z0-9]+'],
				],
				'evolutions/{id}' => [
					'location' => '~* ^/evolutions/{name}$',
					'methods' => ['GET'],
					'params' => ['name' => '[a-z]+'],
				],
			]),
			new \SplFileInfo($destination)
		))->fulfill();
		Assert::contains('location ~ ^/demands/(?<id>[a-z0-9]+)$ {', file_get_contents($destination));
		Assert::contains('location ~* ^/evolutions/(?<name>[a-z]+)$ {', file_get_contents($destination));
	}

	public function testAddingFastCgiParams(): void {
		$destination = FileMock::create();
		(new Scheduling\Task\GenerateNginxRoutes(
			new Configuration\FakeSource([
				'demands/{id}' => [
					'location' => '~ ^/demands/{id}$',
					'methods' => ['GET'],
					'params' => ['id' => 'X', 'name' => 'Y'],
				],
			]),
			new \SplFileInfo($destination)
		))->fulfill();
		Assert::contains('fastcgi_param ROUTE_PARAM_QUERY id=$id&name=$name;', file_get_contents($destination));
		Assert::contains('fastcgi_param ROUTE_NAME "demands/{id}";', file_get_contents($destination));
		Assert::contains('include php.conf', file_get_contents($destination));
	}

	public function testSkippingMissingParams(): void {
		$destination = FileMock::create();
		(new Scheduling\Task\GenerateNginxRoutes(
			new Configuration\FakeSource([
				'demands/{id}' => [
					'location' => '= /demands',
					'methods' => ['GET'],
				],
			]),
			new \SplFileInfo($destination)
		))->fulfill();
		Assert::contains('location = /demands {', file_get_contents($destination));
		Assert::notContains('fastcgi_param ROUTE_PARAM_QUERY', file_get_contents($destination));
	}

	public function testAllowedMethodsWithIncludedOptions(): void {
		$destination = FileMock::create();
		(new Scheduling\Task\GenerateNginxRoutes(
			new Configuration\FakeSource([
				'demands/{id}' => [
					'location' => '~ ^/demands/{id}$',
					'methods' => ['GET', 'PUT'],
					'params' => ['id' => 'X', 'name' => 'Y'],
				],
			]),
			new \SplFileInfo($destination)
		))->fulfill();
		Assert::contains('limit_except GET PUT OPTIONS {', file_get_contents($destination));
	}

	public function testNoDoubledOptions(): void {
		$destination = FileMock::create();
		(new Scheduling\Task\GenerateNginxRoutes(
			new Configuration\FakeSource([
				'demands/{id}' => [
					'location' => '~ ^/demands/{id}$',
					'methods' => ['GET', 'OPTIONS'],
					'params' => ['id' => 'X', 'name' => 'Y'],
				],
			]),
			new \SplFileInfo($destination)
		))->fulfill();
		Assert::contains('limit_except GET OPTIONS {', file_get_contents($destination));
	}

	public function testNoContentRedirectAsDefaultForOptions(): void {
		$destination = FileMock::create();
		(new Scheduling\Task\GenerateNginxRoutes(
			new Configuration\FakeSource([
				'demands/{id}' => [
					'location' => '~ ^/demands/{id}$',
					'methods' => ['GET'],
					'params' => ['id' => 'X', 'name' => 'Y'],
				],
			]),
			new \SplFileInfo($destination)
		))->fulfill();
		Assert::contains('include preflight.conf;', file_get_contents($destination));
	}
}

(new GenerateNginxRoutesTest())->run();
