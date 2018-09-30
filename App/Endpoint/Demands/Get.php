<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	public const SORTS = [
		'id',
		'general.age',
		'general.firstname',
		'general.lastname',
		'general.sex',
		'created_at',
	];

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Hashids\HashidsInterface */
	private $hashids;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		$demands = new Interaction\PublicDemands(
			new Interaction\IndividualDemands(
				$this->seeker,
				$this->connection
			),
			$this->hashids
		);
		$count = $demands->count(new Dataset\EmptySelection());
		return new Response\PartialResponse(
			new Response\PaginatedResponse(
				new Response\JsonResponse(
					new Response\PlainResponse(
						new Misc\JsonPrintedObjects(
							...iterator_to_array(
								$demands->all(
									new Constraint\MappedSelection(
										new Dataset\CombinedSelection(
											new Constraint\AllowedSort(
												new Dataset\RestSort(
													$parameters['sort']
												),
												self::SORTS
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
