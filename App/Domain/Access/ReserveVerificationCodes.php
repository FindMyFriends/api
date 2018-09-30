<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Reserve verification codes which can be given on demand in case the old one has been lost
 * With the "lost" is meant that the code was not received or occurred other issue
 */
final class ReserveVerificationCodes implements VerificationCodes {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param string $email
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\VerificationCode
	 */
	public function generate(string $email): VerificationCode {
		$code = (new Storage\TypedQuery(
			$this->connection,
			'SELECT code
			FROM verification_codes
			WHERE seeker_id = (
				SELECT id
				FROM seekers
				WHERE email IS NOT DISTINCT FROM ?
			)
			AND used_at IS NULL',
			[$email]
		))->field();
		if (strlen((string) $code) !== 0)
			return new ThrowawayVerificationCode($code, $this->connection);
		throw new \UnexpectedValueException('For the given email, there is no valid verification code');
	}
}
