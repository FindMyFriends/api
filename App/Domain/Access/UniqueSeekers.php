<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Encryption;
use Klapuch\Storage;

/**
 * Collection of unique seekers
 */
final class UniqueSeekers implements Seekers {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	public function __construct(Storage\MetaPDO $database, Encryption\Cipher $cipher) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	/**
	 * @param array $credentials
	 * @return \FindMyFriends\Domain\Access\Seeker
	 * @throws \UnexpectedValueException
	 */
	public function join(array $credentials): Seeker {
		if ($this->exists($credentials['email']))
			throw new \UnexpectedValueException(sprintf('Email "%s" already exists', $credentials['email']));
		return (new Storage\Transaction($this->database))->start(function () use ($credentials): Seeker {
			$seeker = (new Storage\TypedQuery(
				$this->database,
				'INSERT INTO seekers (email, password) VALUES
				(?, ?)
				RETURNING *',
				[$credentials['email'], $this->cipher->encryption($credentials['password'])]
			))->row();
			(new Storage\TypedQuery(
				$this->database,
				'SELECT created_base_evolution(
					:seeker,
					:sex,
					:ethnic_group_id,
					:birth_year,
					:firstname,
					:lastname
				)',
				['seeker' => $seeker['id']] + $credentials['general']
			))->execute();
			return new ConstantSeeker((string) $seeker['id'], $seeker);
		});
	}

	private function exists(string $email): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1 FROM seekers WHERE email = ?',
			[$email]
		))->field();
	}
}
