<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;

final class Delete implements Application\View {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function template(array $parameters): Output\Template {
		try {
			(new Evolution\HarnessedChange(
				new Evolution\ExistingChange(
					new Evolution\StoredChange(
						$parameters['id'],
						$this->database
					),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(404)
			))->revert();
			return new Application\RawTemplate(new Response\EmptyResponse());
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}