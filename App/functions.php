<?php
declare(strict_types = 1);

function array_values_recursive(array $values): array {
	return array_reduce(
		$values,
		static function(array $flat, $value): array {
			if (is_array($value)) {
				foreach (array_values_recursive($value) as $nested) {
					$flat[] = $nested;
				}
			} else {
				$flat[] = $value;
			}
			return $flat;
		},
		[]
	);
}

/**
 * @param mixed[] $array
 * @param int|string ...$keys
 * @return bool
 */
function array_keys_exist(array $array, ...$keys): bool {
	foreach ($keys as $key) {
		if (!array_key_exists($key, $array)) {
			return false;
		}
	}
	return true;
}
