<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class Get implements Application\View {
	private $hashids;
	private $url;
	private $database;
	private $role;


	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		\PDO $database,
		Http\Role $role
	) {
		$this->hashids = $hashids;
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
									(new Evolution\FormattedChange(
										new Evolution\HarnessedChange(
											new Evolution\ExistingChange(
												new Evolution\StoredChange(
													$parameters['id'],
													$this->database
												),
												$parameters['id'],
												$this->database
											),
											new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
										),
										$this->hashids
									))->print(new Output\Json())
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