<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demand;

use FindMyFriends\Misc;
use FindMyFriends\Response;
use FindMyFriends\Request;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Output;
use Predis;

final class Put extends V1\Api {
	public function template(array $parameters): Output\Template {
		try {
			$description = json_decode(
				(new Request\JsonRequest(
					new Request\ConcurrentlyControlledRequest(
						new Application\PlainRequest(),
						$this->url,
						new Misc\ETagRedis($this->redis)
					)
				))->body()->serialization(),
				true
			);
			return new Application\RawTemplate(
				new Response\JsonResponse(
					new Response\EmptyResponse(),
					204
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}