<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Notifications;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Activity;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri\Uri;

final class Get implements Application\View {
	public const SCHEMA = __DIR__ . '/schema/get.json';

	public const SORTS = [
		'notified_at',
		'seen_at',
	];

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Uri $url,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->url = $url;
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		$notifications = new Activity\PublicNotifications(
			new Activity\IndividualNotifications($this->seeker, $this->connection)
		);
		$count = $notifications->count(
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
							...iterator_to_array(
								$notifications->receive(
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
