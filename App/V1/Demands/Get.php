<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demands;

use FindMyFriends\Domain;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;

final class Get extends V1\Api {
	private const ALLOWED_SORTS = ['created_at'];

	public function template(array $parameters): Output\Template {
		try {
			return new Application\RawTemplate(
				new Response\HttpResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										(new Domain\CollectiveDemands(
											new Domain\FakeDemands(),
											$this->database
										))->all(
											new Dataset\SqlRestSort(
												$_GET['sort'] ?? '',
												self::ALLOWED_SORTS
											)
										)
									)
								)
							),
							$this->user,
							$this->url
						)
					)
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}