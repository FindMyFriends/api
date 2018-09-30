<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Encryption;
use Klapuch\Storage;

/**
 * Collection of unique seekers
 */
final class UniqueSeekers implements Seekers {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	public function __construct(Storage\Connection $connection, Encryption\Cipher $cipher) {
		$this->connection = $connection;
		$this->cipher = $cipher;
	}

	/**
	 * @param array $credentials
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function join(array $credentials): Seeker {
		return (new Storage\Transaction($this->connection))->start(function () use ($credentials): Seeker {
			$seeker = (new Storage\ApplicationQuery(
				new Storage\TypedQuery(
					$this->connection,
					'INSERT INTO seekers (email, password) VALUES
					(?, ?)
					RETURNING *',
					[$credentials['email'], $this->cipher->encryption($credentials['password'])]
				)
			))->row();
			(new Storage\ApplicationQuery(
				new Storage\TypedQuery(
					$this->connection,
					'INSERT INTO seeker_contacts (seeker_id, facebook, instagram, phone_number) VALUES
					(:seeker, :facebook, :instagram, :phone_number)',
					['seeker' => $seeker['id']] + $credentials['contact']
				)
			))->execute();
			(new Storage\TypedQuery(
				$this->connection,
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
}
