<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Predis;

final class ConcurrentlyCreatedResponse implements Application\Response {
	private $origin;
	private $redis;
	private $uri;

	public function __construct(
		Application\Response $origin,
		Predis\ClientInterface $redis,
		Uri\Uri $uri
	) {
		$this->origin = $origin;
		$this->redis = $redis;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		$this->redis->set($this->uri->path(), new Http\ETag($this->origin->body()));
		return $this->origin->headers();
	}
}