<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/get.json';
	private $url;
	private $database;
	private $role;
	private $hashids;
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Uri\Uri $url,
		\PDO $database,
		Access\User $seeker,
		Http\Role $role
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
		$this->role = $role;
	}

	public function response(array $parameters): Application\Response {
		try {
			$demands = new Domain\PublicDemands(
				new Domain\IndividualDemands(
					$this->seeker,
					$this->database
				),
				$this->hashids
			);
			return new Response\PartialResponse(
				new Response\PaginatedResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										$demands->all(
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
								)
							),
							$this->role
						)
					),
					$parameters['page'],
					new UI\AttainablePagination(
						$parameters['page'],
						$parameters['per_page'],
						$demands->count(new Dataset\EmptySelection())
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
