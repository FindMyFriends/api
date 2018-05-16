<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Works just with securely generated codes
 */
final class SecureVerificationCodes implements VerificationCodes {
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

	public function generate(string $email): VerificationCode {
		$code = (new Storage\TypedQuery(
			$this->database,
			'INSERT INTO verification_codes (seeker_id, code)
			VALUES ((SELECT id FROM seekers WHERE email IS NOT DISTINCT FROM ?), ?)
			RETURNING code',
			[$email, bin2hex(random_bytes(25)) . ':' . sha1($email)]
		))->field();
		return new ThrowawayVerificationCode($code, $this->database);
	}
}
