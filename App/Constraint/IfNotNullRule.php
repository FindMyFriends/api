<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Apply rule only for not null subject
 */
final class IfNotNullRule implements Validation\Rule {
	private $origin;

	public function __construct(Validation\Rule $origin) {
		$this->origin = $origin;
	}

	public function satisfied($subject): bool {
		if ($subject === null)
			return true;
		return $this->origin->satisfied($subject);
	}

	/**
	 * @return mixed|null
	 */
	public function apply($subject) {
		if ($subject === null)
			return $subject;
		return $this->origin->apply($subject);
	}
}
