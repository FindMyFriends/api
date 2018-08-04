<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\Soulmates;

use Elasticsearch;
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

	/** @var mixed[] */
	private $hashids;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	public function __construct(
		array $hashids,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$soulmates = new Search\PublicSoulmates(
			new Search\SuitedSoulmates(
				$parameters['demand_id'],
				$this->elasticsearch,
				$this->database
			),
			$this->hashids
		);
		$count = $soulmates->count(new Dataset\EmptySelection());
		return new Response\PartialResponse(
			new Response\PaginatedResponse(
				new Response\JsonResponse(
					new Response\PlainResponse(
						new Misc\JsonPrintedObjects(
							...iterator_to_array(
								$soulmates->matches(
									new Dataset\CombinedSelection(
										new Constraint\SchemaSort(
											new Dataset\RestSort(
												$parameters['sort']
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
						),
						['X-Total-Count' => $count]
					)
				),
				$parameters['page'],
				new UI\AttainablePagination(
					$parameters['page'],
					$parameters['per_page'],
					$count
				),
				$this->url
			),
			$parameters
		);
	}
}
