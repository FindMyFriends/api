<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Encryption;
use Klapuch\Storage;

/**
 * Secure entrance for entering seekers to the system
 */
final class SecureEntrance implements Entrance {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	public function __construct(Storage\MetaPDO $database, Encryption\Cipher $cipher) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function enter(array $credentials): Seeker {
		['email' => $plainEmail, 'password' => $plainPassword] = array_map('strval', $credentials);
		$seeker = (new Storage\TypedQuery(
			$this->database,
			'SELECT *
			FROM seekers  
			WHERE email IS NOT DISTINCT FROM ?',
			[$plainEmail]
		))->row();
		if (!$this->exists($seeker))
			throw new \UnexpectedValueException(sprintf('Email "%s" does not exist', $plainEmail));
		elseif (!$this->cipher->decrypted($plainPassword, $seeker['password']))
			throw new \UnexpectedValueException('Wrong password');
		if ($this->cipher->deprecated($seeker['password']))
			$this->rehash($plainPassword, $seeker['id']);
		return new ConstantSeeker((string) $seeker['id'], $seeker);
	}

	private function exists(array $row): bool {
		return (bool) $row;
	}

	private function rehash(string $password, int $id): void {
		(new Storage\TypedQuery(
			$this->database,
			'UPDATE seekers
			SET password = ?
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->cipher->encryption($password), $id]
		))->execute();
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
