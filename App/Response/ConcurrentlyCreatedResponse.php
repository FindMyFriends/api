<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;

final class ConcurrentlyCreatedResponse implements Application\Response {
	private $origin;
	private $eTag;
	private $uri;

	public function __construct(
		Application\Response $origin,
		Http\ETag $eTag,
		Uri\Uri $uri
	) {
		$this->origin = $origin;
		$this->eTag = $eTag;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		$this->eTag->set($this->origin->body());
		return ['Location' => $this->uri->reference()] + $this->origin->headers();
	}

	public function status(): int {
		return HTTP_CREATED;
	}
}
