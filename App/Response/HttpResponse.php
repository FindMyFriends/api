<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class HttpResponse implements Application\Response {
	private $origin;
	private $headers;

	public function __construct(Application\Response $origin, array $headers = []) {
		$this->origin = $origin;
		$this->headers = $headers;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		return array_combine(
			array_map([$this, 'unify'], array_keys($this->headers + $this->origin->headers())),
			$this->headers + $this->origin->headers()
		);
	}

	public function status(): int {
		return $this->origin->status();
	}

	// @codingStandardsIgnoreStart Used by array_map
	private function unify(string $field): string {
		return implode(
			'-',
			array_map(
				function(string $field): string {
					return ucfirst(strtolower($field));
				},
				explode('-', $field)
			)
		);
	}
	// @codingStandardsIgnoreEnd
}