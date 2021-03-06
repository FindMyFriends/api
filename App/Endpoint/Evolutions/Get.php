<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolutions;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	public const SORTS = [
		'id',
		'evolved_at',
		'general.firstname',
		'general.lastname',
		'general.sex',
	];

	/** @var \Hashids\HashidsInterface */
	private $hashids;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

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

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$evolution = new Evolution\PublicChain(
			new Evolution\IndividualChain(
				$this->seeker,
				$this->connection
			),
			$this->hashids
		);
		$count = $evolution->count(new Dataset\EmptySelection());
		return new Response\PartialResponse(
			new Response\PaginatedResponse(
				new Response\JsonResponse(
					new Response\PlainResponse(
						new Misc\JsonPrintedObjects(
							static function (Evolution\Change $change, Output\Format $format): Output\Format {
								return $change->print($format);
							},
							...iterator_to_array(
								$evolution->changes(
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
