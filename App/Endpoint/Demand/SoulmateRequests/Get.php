<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	public const SCHEMA = __DIR__ . '/schema/get.json';

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(Uri\Uri $url, Storage\MetaPDO $database) {
		$this->url = $url;
		$this->database = $database;
	}

	public function response(array $parameters): Application\Response {
		$requests = new Search\PublicRequests(
			new Search\SubsequentRequests(
				$parameters['demand_id'],
				$this->database
			)
		);
		return new Response\PartialResponse(
			new Response\PaginatedResponse(
				new Response\JsonResponse(
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
	}
}
