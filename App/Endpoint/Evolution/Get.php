<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;

final class Get implements Application\View {
	/** @var \Hashids\HashidsInterface */
	private $hashids;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		return new Response\PartialResponse(
			new Response\JsonResponse(
				new Response\ConcurrentlyControlledResponse(
					new Response\PlainResponse(
						(new Evolution\PublicChange(
							new Evolution\HarnessedChange(
								new Evolution\OwnedChange(
									new Evolution\StoredChange(
										$parameters['id'],
										$this->connection
									),
									$parameters['id'],
									$this->seeker,
									$this->connection
								),
								new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
							),
							$this->hashids
						))->print(new Output\Json())
					),
					new Http\PostgresETag($this->connection, $this->url)
				)
			),
			$parameters
		);
	}
}
