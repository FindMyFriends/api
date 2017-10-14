<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Predis;

final class ConcurrentlyControlledResponse implements Application\Response {
	private $origin;
	private $uri;
	private $redis;

	public function __construct(
		Application\Response $origin,
		Uri\Uri $uri,
		Predis\ClientInterface $redis
	) {
		$this->origin = $origin;
		$this->uri = $uri;
		$this->redis = $redis;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		if ($this->redis->exists($this->uri->path()))
			return ['ETag' => $this->redis->get($this->uri->path())] + $this->origin->headers();
		return $this->origin->headers();
	}

	public function status(): int {
		return $this->origin->status();
	}
}