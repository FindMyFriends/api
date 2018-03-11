<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Evolutions;

use Elasticsearch;
use FindMyFriends\Constraint;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $hashids;
	private $request;
	private $url;
	private $database;
	private $elasticsearch;
	private $user;

	public function __construct(
		HashidsInterface $hashids,
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch,
		Access\User $user
	) {
		$this->hashids = $hashids;
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
		$this->user = $user;
	}

	public function template(array $parameters): Output\Template {
		try {
			$url = new Http\CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, 'v1/evolutions/{id}'),
				[
					'id' => $this->hashids->encode(
						(new Evolution\SyncChain(
							new Evolution\IndividualChain(
								$this->user,
								$this->database
							),
							$this->elasticsearch
						))->extend(
							(new Validation\ChainedRule(
								new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
								new Constraint\EvolutionRule()
							))->apply(json_decode($this->request->body()->serialization(), true))
						)
					),
				]
			);
			return new Application\RawTemplate(
				new Response\ConcurrentlyCreatedResponse(
					new Response\EmptyResponse(),
					new Http\PostgresETag($this->database, $this->url),
					$url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}