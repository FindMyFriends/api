<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demands;

use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;

final class Get extends V1\Api {
	private const ALLOWED_SORTS = ['created_at'];
	private const DEFAULT_PER_PAGE = 10;

	public function template(array $parameters): Output\Template {
		try {
			$page = intval($_GET['page'] ?? 1);
			$perPage = intval($_GET['per_page'] ?? self::DEFAULT_PER_PAGE);
			$demands = new Domain\CollectiveDemands(
				new Domain\FakeDemands(),
				$this->database
			);
			return new Application\RawTemplate(
				new Response\HttpResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										$demands->all(
											new Dataset\CombinedSelection(
												new Dataset\SqlRestSort(
													$_GET['sort'] ?? '',
													self::ALLOWED_SORTS
												),
												new Dataset\SqlPaging(
													$page,
													$perPage
												)
											)
										)
									)
								)
							),
							$this->user,
							$this->url
						)
					),
					200, //TODO: 206 for not last page
					[
						'Link' => (new UI\AttainablePagination(
							$page,
							$perPage,
							$demands->count(new Dataset\EmptySelection())
						))->print(new Http\HeaderLink($this->url))->serialization(),
					]
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}