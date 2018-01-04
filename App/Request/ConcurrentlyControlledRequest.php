<?php
declare(strict_types = 1);

namespace FindMyFriends\Request;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;

final class ConcurrentlyControlledRequest implements Application\Request {
	private const MATCHES = [
		'If-Match' => true,
		'If-None-Match' => false,
	];
	private $origin;
	private $eTag;

	public function __construct(Application\Request $origin, Http\ETag $eTag) {
		$this->origin = new CachedRequest($origin);
		$this->eTag = $eTag;
	}

	public function body(): Output\Format {
		if ($this->eTag->exists() && !$this->matches($this->eTag->get(), $this->headers()))
			throw new \UnexpectedValueException('ETag does not match your preferences', HTTP_PRECONDITION_FAILED);
		$this->eTag->set($this->origin->body());
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