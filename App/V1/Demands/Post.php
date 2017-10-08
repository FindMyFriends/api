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
					(new Constraint\DemandRule())->apply(
						(new Constraint\StructuredJson(
							new \SplFileInfo(self::SCHEMA)
						))->apply(
							json_decode(
								(new Application\PlainRequest())->body()->serialization(),
								true
							)
						)
					)
				)
			);
			return new Application\RawTemplate(
				new Response\HttpResponse(
					new Response\ConcurrentlyCreatedResponse(
						new Response\JsonResponse(new Response\EmptyResponse()),
						new Http\ETagRedis($this->redis),
						$url
					),
					201,
					['Location' => $url->reference()]
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}