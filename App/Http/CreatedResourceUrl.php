<?php
declare(strict_types = 1);
namespace FindMyFriends\Http;

use Klapuch\Output;
use Klapuch\Uri;

/**
 * Resource used for identification in Location header after successful POST request with 201 code
 */
final class CreatedResourceUrl implements Uri\Uri {
	private const DELIMITER = '/';
	private $origin;
	private $object;

	public function __construct(Uri\Uri $origin, object $object) {
		$this->origin = $origin;
		$this->object = $object;
	}

	public function path(): string {
		$parts = explode(self::DELIMITER, $this->origin->path());
		$replacements = $this->replacements(
			$this->placeholders($parts),
			json_decode(
				$this->object->print(new Output\Json())->serialization(),
				true
			)
		) + $parts;
		ksort($replacements);
		return ltrim(implode(self::DELIMITER, $replacements), self::DELIMITER);
	}

	public function reference(): string {
		return rtrim(
			$this->origin->reference(),
			self::DELIMITER
		) . self::DELIMITER . $this->path();
	}

	/**
	 * Placeholders extracted from parts
	 * @param array $parts
	 * @return array
	 */
	private function placeholders(array $parts): array {
		return array_map(
			function(string $placeholder): string {
				return str_replace(['{', '}'], '', $placeholder);
			},
			preg_grep('~^{.+}$~', $parts)
		);
	}

	/**
	 * Placeholders replaced in parameters
	 * @param array $placeholders
	 * @param array $parameters
	 * @return array
	 */
	private function replacements(array $placeholders, array $parameters): array {
		$lost = $this->lost($placeholders, $parameters);
		if ($lost)
			throw new \UnexpectedValueException($this->format($lost));
		return array_map(
			function(string $placeholder) use ($parameters) {
				return $parameters[$placeholder];
			},
			$placeholders
		);
	}

	/**
	 * Placeholders without counterpart in parameters
	 * @param array $placeholders
	 * @param array $parameters
	 * @return array
	 */
	private function lost(array $placeholders, array $parameters): array {
		return array_filter(
			$placeholders,
			function(string $placeholder) use ($parameters): bool {
				return !isset($parameters[$placeholder]);
			}
		);
	}

	/**
	 * Format message with the lost parameters/placeholders
	 * @param array $lost
	 * @return string
	 */
	private function format(array $lost): string {
		$plural = count($lost) > 1;
		return sprintf(
			'Placeholder%s "%s" %s unused',
			$plural ? 's' : '',
			implode(', ', $lost),
			$plural ? 'are' : 'is'
		);
	}
}