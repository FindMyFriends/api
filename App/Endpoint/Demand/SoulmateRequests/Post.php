<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;

final class Post implements Application\View {
	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \PhpAmqpLib\Connection\AbstractConnection */
	private $rabbitMq;

	public function __construct(
		Uri\Uri $url,
		Storage\Connection $connection,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq
	) {
		$this->url = $url;
		$this->connection = $connection;
		$this->rabbitMq = $rabbitMq;
	}

	public function response(array $parameters): Application\Response {
		(new Search\HarnessedPublisher(
			new Search\RefreshablePublisher(
				new Search\AmqpPublisher($this->rabbitMq),
				$this->connection
			),
			new Misc\ApiErrorCallback(HTTP_TOO_MANY_REQUESTS)
		))->publish($parameters['demand_id']);
		return new class ($this->url) implements Application\Response {
			/** @var \Klapuch\Uri\Uri */
			private $url;

			public function __construct(Uri\Uri $url) {
				$this->url = $url;
			}

			public function body(): Output\Format {
				return new Output\EmptyFormat();
			}

			public function headers(): array {
				return [];
			}

			public function status(): int {
				return HTTP_ACCEPTED;
			}
		};
	}
}
