<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Seeker which can be publicly shown, without sensitive data
 */
final class PublicSeeker implements Seeker {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $origin;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $foreigner;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Seeker $origin, Seeker $foreigner, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->foreigner = $foreigner;
		$this->connection = $connection;
	}

	public function id(): string {
		return $this->foreigner->id();
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function properties(): array {
		if (!$this->known())
			throw new \UnexpectedValueException(sprintf('Seeker %d is unknown', $this->foreigner->id()));
		$properties = (new PubliclyPrivateSeeker($this->origin, $this->connection))->properties();
		unset($properties['email']);
		return $properties;
	}

	private function known(): bool {
		return (bool) (new Storage\TypedQuery(
			$this->connection,
			'SELECT 1 FROM exposed_seekers WHERE seeker_id = ? AND exposed_seeker_id = ?',
			[$this->origin->id(), $this->foreigner->id()]
		))->field();
	}
}
