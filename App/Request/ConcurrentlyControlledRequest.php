<?php
declare(strict_types = 1);
namespace FindMyFriends\Request;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Predis;

final class ConcurrentlyControlledRequest implements Application\Request {
	private const MATCHES = [
		'If-Match' => true,
		'If-None-Match' => false,
	];
	private $origin;
	private $uri;
	private $redis;

	public function __construct(
		Application\Request $origin,
		Uri\Uri $uri,
		Predis\ClientInterface $redis
	) {
		$this->origin = new CachedRequest($origin);
		$this->uri = $uri;
		$this->redis = $redis;
	}

	public function body(): Output\Format {
		if ($this->redis->exists($this->uri->path()) && !$this->matches($this->redis->get($this->uri->path()), $this->headers()))
			throw new \UnexpectedValueException('ETag does not match your preferences');
		$this->redis->set($this->uri->path(), new Http\ETag($this->origin->body()));
		return $this->origin->body();
	}

	public function headers(): array {
		return $this->origin->headers();
	}

	/**
	 * Does the ETag matches with some passed header?
	 * @param string $eTag
	 * @param array $headers
	 * @return bool
	 */
	private function matches(string $eTag, array $headers): bool {
		$field = key(array_intersect_key($headers, self::MATCHES));
		return isset($headers[$field]) ? self::MATCHES[$field] === ($headers[$field] === $eTag) : false;
	}
}