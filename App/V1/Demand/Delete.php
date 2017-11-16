<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;

final class Delete implements Application\View {
	private $database;
	private $user;

	public function __construct(\PDO $database, Access\User $user) {
		$this->database = $database;
		$this->user = $user;
	}

	public function template(array $parameters): Output\Template {
		try {
			(new Domain\ChainedDemand(
				new Domain\HarnessedDemand(
					new Domain\ExistingDemand(
						new Domain\FakeDemand(),
						$parameters['id'],
						$this->database
					),
					new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
				),
				new Domain\HarnessedDemand(
					new Domain\OwnedDemand(
						new Domain\FakeDemand(),
						$parameters['id'],
						$this->user,
						$this->database
					),
					new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
				),
				new Domain\StoredDemand($parameters['id'], $this->database)
			))->retract();
			return new Application\RawTemplate(new Response\EmptyResponse());
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}