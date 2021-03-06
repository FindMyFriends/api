<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class CachedResponse implements Application\Response {
	/** @var \Klapuch\Output\Format|null */
	private $body;

	/** @var mixed[]|null */
	private $headers;

	/** @var int|null */
	private $status;

	/** @var \Klapuch\Application\Response */
	private $origin;

	public function __construct(Application\Response $origin) {
		$this->origin = $origin;
	}

	public function body(): Output\Format {
		if ($this->body === null)
			$this->body = $this->origin->body();
		return $this->body;
	}

	public function headers(): array {
		if ($this->headers === null)
			$this->headers = $this->origin->headers();
		return $this->headers;
	}

	public function status(): int {
		if ($this->status === null)
			$this->status = $this->origin->status();
		return $this->status;
	}
}
