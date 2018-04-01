<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demand\SoulmateRequests;

use FindMyFriends\Domain\Search;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	private const ALLOWED_SORTS = ['searched_at'],
		ALLOWED_FILTERS = ['status'];
	private $url;
	private $database;
	private $role;

	public function __construct(
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Http\Role $role
	) {
		$this->url = $url;
		$this->database = $database;
		$this->role = $role;
	}

	public function template(array $parameters): Output\Template {
		try {
			$requests = new Search\PublicRequests(
				new Search\SubsequentRequests(
					$parameters['demand_id'],
					$this->database
				)
			);
			return new Application\RawTemplate(
				new Response\PartialResponse(
					new Response\PaginatedResponse(
						new Response\JsonResponse(
							new Response\JsonApiAuthentication(
								new Response\PlainResponse(
									new Misc\JsonPrintedObjects(
										...iterator_to_array(
											$requests->all(
												new Dataset\CombinedSelection(
													new Dataset\RestSort(
														$parameters['sort'],
														self::ALLOWED_SORTS
													),
													new Dataset\RestFilter(
														$parameters,
														self::ALLOWED_FILTERS
													),
													new Dataset\RestPaging(
														$parameters['page'],
														$parameters['per_page']
													)
												)
											)
										)
									)
								),
								$this->role
							)
						),
						$parameters['page'],
						new UI\AttainablePagination(
							$parameters['page'],
							$parameters['per_page'],
							$requests->count(
								new Dataset\RestFilter(
									$parameters,
									self::ALLOWED_FILTERS
								)
							)
						),
						$this->url
					),
					$parameters
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}