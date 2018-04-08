<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class Get implements Application\View {
	private $hashids;
	private $url;
	private $database;
	private $seeker;
	private $role;

	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		\PDO $database,
		Access\User $seeker,
		Http\Role $role
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
		$this->role = $role;
	}

	public function response(array $parameters): Application\Response {
		try {
			return new Response\PartialResponse(
				new Response\JsonResponse(
					new Response\ConcurrentlyControlledResponse(
						new Response\CachedResponse(
							new Response\JsonApiAuthentication(
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
								$this->role
							)
						),
						new Http\PostgresETag($this->database, $this->url)
					)
				),
				$parameters
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
