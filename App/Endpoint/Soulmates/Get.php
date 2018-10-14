<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Soulmates;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri\Uri;

final class Get implements Application\View {
	public const SCHEMA = __DIR__ . '/schema/get.json';

	public const SORTS = [
		'searched_at',
		'related_at',
	];

	/** @var mixed[] */
	private $hashids;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		array $hashids,
		Uri $url,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$soulmates = new Search\PublicSoulmates(
			new Search\OwnedSoulmates($this->seeker, $this->connection),
			$this->hashids
		);
		$count = $soulmates->count(
			new Constraint\SchemaFilter(
				new Dataset\RestFilter($parameters),
				new \SplFileInfo(self::SCHEMA)
			)
		);
		return new Response\PartialResponse(
			new Response\PaginatedResponse(
				new Response\JsonResponse(
					new Response\PlainResponse(
						new Misc\JsonPrintedObjects(
							static function (Search\Soulmate $soulmate, Output\Format $format): Output\Format {
								return $soulmate->print($format);
							},
							...iterator_to_array(
								$soulmates->matches(
									new Constraint\MappedSelection(
										new Dataset\CombinedSelection(
											new Constraint\AllowedSort(
												new Dataset\RestSort($parameters['sort']),
												self::SORTS
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
