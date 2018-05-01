<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demand\SoulmateRequests;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Search;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	public const SCHEMA = __DIR__ . '/schema/get.json';
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

	public function response(array $parameters): Application\Response {
		try {
			$requests = new Search\PublicRequests(
				new Search\SubsequentRequests(
					$parameters['demand_id'],
					$this->database
				)
			);
			return new Response\PartialResponse(
				new Response\PaginatedResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										$requests->all(
											new Dataset\CombinedSelection(
												new Constraint\SchemaSort(
													new Dataset\RestSort(
														$parameters['sort']
													),
													new \SplFileInfo(self::SCHEMA)
												),
												new Constraint\SchemaFilter(
													new Dataset\RestFilter(
														$parameters
													),
													new \SplFileInfo(self::SCHEMA)
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
							new Constraint\SchemaFilter(
								new Dataset\RestFilter($parameters),
								new \SplFileInfo(self::SCHEMA)
							)
						)
					),
					$this->url
				),
				$parameters
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
