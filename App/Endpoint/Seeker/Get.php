<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Seeker;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint\Seekers;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;

final class Get implements Application\View {
	public const SCHEMA = __DIR__ . '/schema/get.json';

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(Storage\Connection $connection, Access\Seeker $seeker) {
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		if (strval($parameters['id']) === $this->seeker->id()) // this is some kind of simple "redirect"
			return (new Seekers\Me\Get($this->connection, $this->seeker))->response($parameters);
		return new Response\PartialResponse(
			new Response\JsonResponse(
				new Response\PlainResponse(
					new Output\Json(
						(new Access\HarnessedSeeker(
							new Access\PublicSeeker(
								$this->seeker,
								new Access\FakeSeeker((string) $parameters['id']),
								$this->connection
							),
							new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
						))->properties()
					)
				)
			),
			$parameters
		);
	}
}
