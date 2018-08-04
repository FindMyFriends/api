<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\Soulmates;

use Elasticsearch;
use FindMyFriends\Domain\Search;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Head implements Application\View {
	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	public function __construct(
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Elasticsearch\Client $elasticsearch
	) {
		$this->url = $url;
		$this->database = $database;
		$this->elasticsearch = $elasticsearch;
	}

	public function response(array $parameters): Application\Response {
		$count = (new Search\SuitedSoulmates(
			$parameters['demand_id'],
			$this->elasticsearch,
			$this->database
		))->count(new Dataset\EmptySelection());
		return new Response\PaginatedResponse(
			new Response\PlainResponse(
				new Output\EmptyFormat(),
				['X-Total-Count' => $count, 'Content-Type' => 'text/plain']
			),
			$parameters['page'],
			new UI\AttainablePagination(
				$parameters['page'],
				$parameters['per_page'],
				$count
			),
			$this->url
		);
	}
}
