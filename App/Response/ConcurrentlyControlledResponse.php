<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;

final class ConcurrentlyControlledResponse implements Application\Response {
	private $origin;
	private $eTag;

	public function __construct(Application\Response $origin, Http\ETag $eTag) {
		$this->origin = $origin;
		$this->eTag = $eTag;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		if ($this->eTag->exists())
			return ['ETag' => $this->eTag->get()] + $this->origin->headers();
		return $this->origin->headers();
	}

	public function status(): int {
		return $this->origin->status();
	}
}