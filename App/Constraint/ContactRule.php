<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for contact
 */
final class ContactRule implements Validation\Rule {
	/**
	 * @param mixed[] $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return false; // not used
	}

	/**
	 * @param mixed[] $contact
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function apply($contact): array {
		if ($this->available($contact))
			return $contact;
		throw new \UnexpectedValueException('At least one contact must be specified.');
	}

	private function available(array $contact): bool {
		return !is_null(
			$contact['facebook']
				?? $contact['instagram']
				?? $contact['phone_number']
		);
	}
}
