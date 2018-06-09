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
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	public const SORTS = [
		'id',
		'evolved_at',
	];
	private $hashids;
	private $url;
	private $database;
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		\PDO $database,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		try {
			$evolution = new Evolution\PublicChain(
				new Evolution\IndividualChain(
					$this->seeker,
					$this->database
				),
				$this->hashids
			);
			return new Response\PartialResponse(
				new Response\PaginatedResponse(
					new Response\JsonResponse(
						new Response\PlainResponse(
							new Misc\JsonPrintedObjects(
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
							)
						)
					),
					$parameters['page'],
					new UI\AttainablePagination(
						$parameters['page'],
						$parameters['per_page'],
						$evolution->count(new Dataset\EmptySelection())
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
