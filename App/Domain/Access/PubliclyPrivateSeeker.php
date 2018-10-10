<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Seeker which can be publicly shown, but includes potential private data to owner
 */
final class PubliclyPrivateSeeker implements Seeker {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $origin;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Seeker $origin, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->connection = $connection;
	}

	public function id(): string {
		return $this->origin->id();
	}

	public function properties(): array {
		$properties = (new Storage\TypedQuery(
			$this->connection,
			'SELECT seekers.email, contacts.facebook, contacts.instagram, contacts.phone_number
			FROM seekers
			JOIN seeker_contacts AS contacts ON contacts.seeker_id = seekers.id
			WHERE seekers.id = ?',
			[$this->id()]
		))->row();
		return [
			'email' => $properties['email'],
			'contact' => [
				'facebook' => $properties['facebook'],
				'instagram' => $properties['instagram'],
				'phone_number' => $properties['phone_number'],
			],
		];
	}
}
