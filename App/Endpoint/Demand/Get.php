<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
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

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
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
						(new Domain\PublicDemand(
							new Domain\HarnessedDemand(
								new Domain\OwnedDemand(
									new Domain\StoredDemand(
										$parameters['id'],
										$this->database
									),
									$parameters['id'],
									$this->seeker,
									$this->database
								),
								new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
							),
							$this->hashids
						))->print(new Output\Json())
					),
					new Http\PostgresETag($this->database, $this->url)
				)
			),
			$parameters
		);
	}
}
