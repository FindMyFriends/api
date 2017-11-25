<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleHand implements Sample {
	private const CARES = ['low', 'medium', 'high'];
	private const COLORS = ['blue', 'black'];
	private const HAND_CARES = ['dry', 'greasy', 'normal'];
	private const VEIN_VISIBILITY = ['visible', 'invisible'];
	private const JOINT_VISIBILITY = ['visible', 'invisible'];
	private const HAND_HAIR = ['few', 'a lot'];
	private $database;
	private $hand;

	public function __construct(\PDO $database, array $hand = []) {
		$this->database = $database;
		$this->hand = $hand;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO hands (nails, care, veins, joint, hair) VALUES 
			(ROW(?, ?, ?)::nail, ?, ?, ?, ?)
			RETURNING id',
			[
				$this->hand['nails']['color'] ?? self::COLORS[array_rand(self::COLORS)],
				$this->hand['nails']['length'] ?? mt_rand(),
				$this->hand['nails']['care'] ?? self::CARES[array_rand(self::CARES)],
				$this->hand['care'] ?? self::HAND_CARES[array_rand(self::HAND_CARES)],
				$this->hand['veins'] ?? self::VEIN_VISIBILITY[array_rand(self::VEIN_VISIBILITY)],
				$this->hand['joint'] ?? self::JOINT_VISIBILITY[array_rand(self::JOINT_VISIBILITY)],
				$this->hand['hair'] ?? self::HAND_HAIR[array_rand(self::HAND_HAIR)],
			]
		))->row();
	}
}