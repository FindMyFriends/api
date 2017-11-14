<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demand;

use FindMyFriends\Domain;
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
			(new Domain\HarnessedDemand(
				new Domain\ExistingDemand(
					new Domain\StoredDemand(
						$parameters['id'],
						$this->database
					),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			))->retract();
			return new Application\RawTemplate(new Response\EmptyResponse());
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}