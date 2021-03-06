<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Soulmate belongs to me
 */
final class OwnedSoulmate implements Soulmate {
	/** @var \FindMyFriends\Domain\Search\Soulmate */
	private $origin;

	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	public function __construct(
		Soulmate $origin,
		int $id,
		Access\Seeker $owner,
		Storage\Connection $connection
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->connection = $connection;
		$this->owner = $owner;
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->owned($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	/**
	 * @param bool $correct
	 * @throws \UnexpectedValueException
	 */
	public function clarify(bool $correct): void {
		if (!$this->owned($this->id) || !$this->demanding($this->id))
			throw $this->exception($this->id);
		$this->origin->clarify($correct);
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function expose(): void {
		if (!$this->owned($this->id) || !$this->evolving($this->id))
			throw $this->exception($this->id);
		$this->origin->expose();
	}

	private function demanding(int $id): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_demanding_soulmate_owned(:soulmate, :seeker)',
			['soulmate' => $id, 'seeker' => $this->owner->id()]
		))->field();
	}

	private function evolving(int $id): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_evolving_soulmate_owned(:soulmate, :seeker)',
			['soulmate' => $id, 'seeker' => $this->owner->id()]
		))->field();
	}

	private function owned(int $id): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_soulmate_owned(:soulmate, :seeker)',
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
