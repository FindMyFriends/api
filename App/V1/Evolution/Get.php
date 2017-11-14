<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Predis;

final class Get implements Application\View {
	private $url;
	private $database;
	private $user;
	private $redis;

	public function __construct(
		Uri\Uri $url,
		\PDO $database,
		Access\User $user,
		Predis\ClientInterface $redis
	) {
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
		$this->redis = $redis;
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
										)
									))->print(new Output\Json)
								),
								$this->user,
								$this->url
							)
						),
						$this->url,
						new Http\ETagRedis($this->redis)
					)
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}