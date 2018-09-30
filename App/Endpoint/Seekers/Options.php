<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Seekers;

use FindMyFriends\Response;
use FindMyFriends\Schema;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Predis;

final class Options implements Application\View {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Predis\ClientInterface */
	private $redis;

	public function __construct(Storage\Connection $connection, Predis\ClientInterface $redis) {
		$this->connection = $connection;
		$this->redis = $redis;
	}

	public function response(array $parameters): Application\Response {
		return new Response\JsonResponse(
			new Response\PlainResponse(
				new Output\Json(
					(new Schema\Seeker\ExplainedTableEnums(
						$this->connection,
						$this->redis
					))->values()
				)
			)
		);
	}
}
