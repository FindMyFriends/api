<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demands;

use FindMyFriends\Domain;
use FindMyFriends\Misc;
use FindMyFriends\Request;
use FindMyFriends\Response;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class Post extends V1\Api {
	public function template(array $parameters): Output\Template {
		try {
			$url = new Misc\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/demands/{id}'),
				(new Domain\OwnedDemands(
					$this->user,
					$this->database
				))->ask(
					json_decode(
						(new Request\StructuredJsonRequest(
							new Request\JsonRequest(new Application\PlainRequest()),
							new \SplFileInfo(__DIR__ . '/schema/post.json')
						))->body()->serialization(),
						true
					)
				)
			);
			return new Application\RawTemplate(
				new Response\HttpResponse(
					new Response\ConcurrentlyCreatedResponse(
						new Response\JsonResponse(new Response\EmptyResponse()),
						new Misc\ETagRedis($this->redis),
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