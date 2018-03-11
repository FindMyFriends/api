<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Evolutions;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	private $hashids;
	private $url;
	private $database;
	private $user;
	private $role;


	public function __construct(HashidsInterface $hashids, Uri\Uri $url, \PDO $database, Access\User $user, Http\Role $role) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
		$this->role = $role;
	}

	public function template(array $parameters): Output\Template {
		try {
			$evolution = new Evolution\PublicChain(
				new Evolution\IndividualChain(
					$this->user,
					$this->database
				),
				$this->hashids
			);
			return new Application\RawTemplate(
				new Response\PaginatedResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										$evolution->changes(
											new Dataset\CombinedSelection(
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
						$evolution->count(new Dataset\EmptySelection())
					),
					$this->url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}