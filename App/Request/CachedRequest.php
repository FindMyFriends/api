<?php
declare(strict_types = 1);

namespace FindMyFriends\Request;

use Klapuch\Application;
use Klapuch\Output;

final class CachedRequest implements Application\Request {
	private $body;
	private $headers;
	private $origin;

	public function __construct(Application\Request $origin) {
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
}