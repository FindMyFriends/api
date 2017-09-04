<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class HttpResponse implements Application\Response {
	private const OK = 200;
	private $origin;
	private $code;
	private $headers;

	public function __construct(
		Application\Response $origin,
		int $code = self::OK,
		array $headers = []
	) {
		$this->origin = $origin;
		$this->code = $code;
		$this->headers = $headers;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		http_response_code($this->code);
		return array_combine(
			array_map([$this, 'unify'], array_keys($this->headers + $this->origin->headers())),
			$this->headers + $this->origin->headers()
		);
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