<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class Get implements Application\View {
	private $url;
	private $database;
	private $role;

	public function __construct(
		Uri\Uri $url,
		\PDO $database,
		Http\Role $role
	) {
		$this->url = $url;
		$this->database = $database;
		$this->role = $role;
	}

	public function template(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\JsonResponse(
					new Response\ConcurrentlyControlledResponse(
						new Response\CachedResponse(
							new Response\JsonApiAuthentication(
								new Response\PlainResponse(
									(new Domain\FormattedDemand(
										new Domain\HarnessedDemand(
											new Domain\ExistingDemand(
												new Domain\StoredDemand(
													$parameters['id'],
													$this->database
												),
												$parameters['id'],
												$this->database
											),
											new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
										)
									))->print(new Output\Json)
								),
								$this->role
							)
						),
						new Http\PostgresETag($this->database, $this->url)
					)
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}