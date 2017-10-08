<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demand;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Request;
use FindMyFriends\Response;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Output;

final class Put extends V1\Api {
	private const SCHEMA = __DIR__ . '/schema/put.json';

	public function template(array $parameters): Output\Template {
		try {
			(new Domain\StoredDemand(
				$parameters['id'],
				$this->database
			))->reconsider(
				(new Constraint\DemandRule())->apply(
					(new Constraint\StructuredJson(
						new \SplFileInfo(self::SCHEMA)
					))->apply(
						json_decode(
							(new Request\ConcurrentlyControlledRequest(
								new Request\CachedRequest(
									new Application\PlainRequest()
								),
								$this->url,
								new Http\ETagRedis($this->redis)
							))->body()->serialization(),
							true
						)
					)
				)
			);
			return new Application\RawTemplate(
				new Response\HttpResponse(
					new Response\EmptyResponse(),
					204
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}