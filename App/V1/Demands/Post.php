<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Response;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;

final class Post extends V1\Api {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	public function template(array $parameters): Output\Template {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/demands/{id}'),
				(new Domain\OwnedDemands(
					$this->user,
					$this->database
				))->ask(
					(new Validation\ChainedRule(
						new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
						new Constraint\DemandRule()
					))->apply(
						json_decode(
							(new Application\PlainRequest())->body()->serialization(),
							true
						)
					)
				)
			);
			return new Application\RawTemplate(
				new Response\ConcurrentlyCreatedResponse(
					new Response\JsonResponse(new Response\EmptyResponse()),
					new Http\ETagRedis($this->redis),
					$url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}