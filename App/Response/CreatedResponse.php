<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class CreatedResponse implements Application\Response {
	/** @var \Klapuch\Application\Response */
	private $origin;

	/** @var \Klapuch\Uri\Uri */
	private $uri;

	public function __construct(Application\Response $origin, Uri\Uri $uri) {
		$this->origin = $origin;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		return ['Location' => $this->uri->reference()] + $this->origin->headers();
	}

	public function status(): int {
		return HTTP_CREATED;
	}
}
