<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class TypeRuleTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPassingOnAllValuesInEnum() {
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE name_type AS ENUM ('Dom', 'Kat')"
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE brand_type AS ENUM ('Dell', 'Casio')"
		))->execute();
		$value = ['names' => 'Dom', 'brands' => 'Dell'];
		$types = ['names' => 'name_type', 'brands' => 'brand_type'];
		Assert::true((new Constraint\TypeRule($this->database, $types))->satisfied($value));
		Assert::noError(function() use ($value, $types) {
			(new Constraint\TypeRule($this->database, $types))->apply($value);
		});
	}

	public function testFailingOnAnyValueOutOfEnum() {
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE name_type AS ENUM ('Dom', 'Kat')"
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE brand_type AS ENUM ('Dell', 'Casio')"
		))->execute();
		$value = ['names' => 'Dom', 'brands' => 'foo'];
		$types = ['names' => 'name_type', 'brands' => 'brand_type'];
		Assert::false((new Constraint\TypeRule($this->database, $types))->satisfied($value));
		Assert::exception(function() use ($value, $types) {
			(new Constraint\TypeRule($this->database, $types))->apply($value);
		}, \UnexpectedValueException::class, "'brands' must be one of: 'Dell', 'Casio' - 'foo' was given");
	}
}

(new TypeRuleTest())->run();