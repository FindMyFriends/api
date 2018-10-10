<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Seekers\Me;

use FindMyFriends\Domain\Access;
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
		return new Response\PartialResponse(
			new Response\JsonResponse(
				new Response\PlainResponse(
					new Output\Json(
						(new Access\PubliclyPrivateSeeker(
							$this->seeker,
							$this->connection
						))->properties()
					)
				)
			),
			$parameters
		);
	}
}
