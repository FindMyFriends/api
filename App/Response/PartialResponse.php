<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;

/**
 * Partial response
 */
final class PartialResponse implements Application\Response {
	private const PARAMETER = 'fields';

	/** @var \Klapuch\Application\Response */
	private $origin;

	/** @var mixed[] */
	private $parameters;

	public function __construct(
		Application\Response $origin,
		array $parameters
	) {
		$this->origin = $origin;
		$this->parameters = $parameters;
	}

	public function body(): Output\Format {
		return $this->origin->body()->adjusted(null, function (array $output): array {
			return (new Dataset\PartialSelection(
				$this->parameters[self::PARAMETER] ?? '',
				$output
			))->criteria();
		});
	}

	public function headers(): array {
		return $this->origin->headers();
	}

	public function status(): int {
		return $this->origin->status();
	}
}
