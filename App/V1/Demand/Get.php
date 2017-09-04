<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demand;

use FindMyFriends\Domain;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Output;

final class Get extends V1\Api {
	public function template(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\HttpResponse(
					new Response\JsonResponse(
						new Response\ConcurrentlyControlledResponse(
							new Response\CachedResponse(
								new Response\JsonApiAuthentication(
									new Response\PlainResponse(
										(new Domain\FormattedDemand(
											new Domain\ExistingDemand(
												new Domain\StoredDemand(
													$parameters['id'],
													$this->database
												),
												$parameters['id'],
												$this->database
											)
										))->print(new Output\Json)
									),
									$this->user,
									$this->url
								)
							),
							$this->url,
							new Misc\ETagRedis($this->redis)
						)
					)
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}