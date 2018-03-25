<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Soulmate belongs to me
 */
final class OwnedSoulmate implements Soulmate {
	private $origin;
	private $id;
	private $database;
	private $owner;

	public function __construct(
		Soulmate $origin,
		int $id,
		Access\User $owner,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
		$this->owner = $owner;
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->owned($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	public function clarify(array $clarification): void {
		if (!$this->owned($this->id))
			throw $this->exception($this->id);
		$this->origin->clarify($clarification);
	}

	private function owned(int $id): bool {
		return (new Storage\NativeQuery(
			$this->database,
			'SELECT is_soulmate_permitted(:soulmate, :seeker)',
			['soulmate' => $id, 'seeker' => $this->owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'This is not your soulmate',
			0,
			new \UnexpectedValueException(sprintf('%d is not your soulmate', $id))
		);
	}
}