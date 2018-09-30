<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolutions;

use FindMyFriends\Domain\Access;
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

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Storage\Connection $connection,
		Predis\ClientInterface $redis,
		Access\Seeker $seeker
	) {
		$this->connection = $connection;
		$this->redis = $redis;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		return new Response\JsonResponse(
			new Response\PlainResponse(
				new Output\Json([
					'options' => (new Schema\Evolution\ExplainedTableEnums(
						$this->connection,
						$this->redis
					))->values(),
					'columns' => (new Schema\Evolution\PrioritizedColumns(
						$this->connection,
						$this->seeker
					))->values(),
				])
			)
		);
	}
}
