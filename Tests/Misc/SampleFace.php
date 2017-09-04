<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SampleFace implements Sample {
	private const CARES = ['low', 'medium', 'high'];
	private const COLORS = ['blue', 'black'];
	private $database;
	private $face;

	public function __construct(\PDO $database, array $face = []) {
		$this->database = $database;
		$this->face = $face;
	}

	public function try(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO faces (teeth, freckles, complexion, beard, acne, shape, hair, eyebrow, left_eye, right_eye) VALUES 
			(ROW(?, ?)::tooth, ?, ?, ?, ?, ?, ROW(?, ?, ?, ?, ?, ?)::hair, ?, ROW(?, ?)::eye, ROW(?, ?)::eye)
			RETURNING id',
			[
				$this->face['teeth']['care'] ?? self::CARES[array_rand(self::CARES)],
				$this->face['teeth']['braces'] ?? (bool) mt_rand(0, 1),
				$this->face['freckles'] ?? (bool) mt_rand(0, 1),
				$this->face['complexion'] ?? self::CARES[array_rand(self::CARES)],
				$this->face['beard'] ?? bin2hex(random_bytes(40)),
				$this->face['acne'] ?? (bool) mt_rand(0, 1),
				$this->face['shape'] ?? bin2hex(random_bytes(40)),
				$this->face['hair']['style'] ?? bin2hex(random_bytes(40)),
				$this->face['hair']['color'] ?? self::COLORS[array_rand(self::COLORS)],
				$this->face['hair']['length'] ?? mt_rand(),
				$this->face['hair']['highlights'] ?? (bool) mt_rand(0, 1),
				$this->face['hair']['roots'] ?? (bool) mt_rand(0, 1),
				$this->face['hair']['nature'] ?? (bool) mt_rand(0, 1),
				$this->face['eyebrow'] ?? bin2hex(random_bytes(40)),
				$this->face['left_eye']['color'] ?? self::COLORS[array_rand(self::COLORS)],
				$this->face['left_eye']['lenses'] ?? (bool) mt_rand(0, 1),
				$this->face['right_eye']['color'] ?? self::COLORS[array_rand(self::COLORS)],
				$this->face['right_eye']['lenses'] ?? (bool) mt_rand(0, 1),
			]
		))->row();
	}
}