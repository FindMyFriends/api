<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

use Klapuch\Output;
use Klapuch\Uri;

/**
 * Format generating links for headers in format
 * <https://example.com>; rel="homepage", ...
 */
final class HeaderLink implements Output\Format {
	private $uri;
	private $moves;

	public function __construct(Uri\Uri $uri, array $moves = []) {
		$this->uri = $uri;
		$this->moves = $moves;
	}

	public function with($tag, $content = null): Output\Format {
		return new self($this->uri, [$tag => $content] + $this->moves);
	}

	public function serialization(): string {
		return implode(
			', ',
			array_map(
				function(string $direction, int $page): string {
					return sprintf(
						'<%s/%s?%s>; rel="%s"',
						rtrim($this->uri->reference(), '/'),
						ltrim($this->uri->path(), '/'),
						http_build_query(
							['page' => $page] + $this->uri->query(),
							'',
							'&',
							PHP_QUERY_RFC3986
						),
						$direction
					);
				},
				array_keys($this->moves),
				$this->moves
			)
		);
	}

	public function adjusted($tag, callable $adjustment): Output\Format {
		return new self(
			$this->uri,
			[$tag => call_user_func($adjustment, $this->moves[$tag])] + $this->moves
		);
	}
}