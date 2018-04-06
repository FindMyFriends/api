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

	public function testFailingOnValueOutOfEnum() {
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE this_is_type AS ENUM ('Dom', 'Kat')"
		))->execute();
		$value = ['names' => 'foo'];
		$types = ['names' => 'this_is_type'];
		Assert::false((new Constraint\TypeRule($this->database, $types))->satisfied($value));
		Assert::exception(function() use ($value, $types) {
			(new Constraint\TypeRule($this->database, $types))->apply($value);
		}, \UnexpectedValueException::class, '"this_is_type" must be one of: "Dom", "Kat" - "foo" was given');
	}

	public function testPassingOnValueInType() {
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE this_is_type AS ENUM ('Dom', 'Kat')"
		))->execute();
		$value = ['names' => 'Dom'];
		$types = ['names' => 'this_is_type'];
		Assert::true((new Constraint\TypeRule($this->database, $types))->satisfied($value));
		Assert::noError(function() use ($value, $types) {
			(new Constraint\TypeRule($this->database, $types))->apply($value);
		});
	}

	public function testMultiplePassingValues() {
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE this_is_type AS ENUM ('Dom', 'Kat')"
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE this_is_type2 AS ENUM ('Dell', 'Casio')"
		))->execute();
		$value = ['names' => 'Dom', 'brands' => 'Dell'];
		$types = ['names' => 'this_is_type', 'brands' => 'this_is_type2'];
		Assert::true((new Constraint\TypeRule($this->database, $types))->satisfied($value));
		Assert::noError(function() use ($value, $types) {
			(new Constraint\TypeRule($this->database, $types))->apply($value);
		});
	}

	public function testFailingOnAnyValueOutOfEnum() {
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE this_is_type AS ENUM ('Dom', 'Kat')"
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			"CREATE TYPE this_is_type2 AS ENUM ('Dell', 'Casio')"
		))->execute();
		$value = ['names' => 'Dom', 'brands' => 'foo'];
		$types = ['names' => 'this_is_type', 'brands' => 'this_is_type2'];
		Assert::false((new Constraint\TypeRule($this->database, $types))->satisfied($value));
		Assert::exception(function() use ($value, $types) {
			(new Constraint\TypeRule($this->database, $types))->apply($value);
		}, \UnexpectedValueException::class, '"this_is_type2" must be one of: "Dell", "Casio" - "foo" was given');
	}
}

(new TypeRuleTest())->run();