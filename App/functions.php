<?php
declare(strict_types = 1);

function array_values_recursive(array $values): array {
	return array_reduce(
		$values,
		function(array $flat, $value): array {
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
