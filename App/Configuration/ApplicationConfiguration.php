<?php
declare(strict_types = 1);

namespace FindMyFriends\Configuration;

use Klapuch\Configuration;

/**
 * Configuration for whole application
 */
final class ApplicationConfiguration implements Configuration\Source {
	private const CONFIGURATION = __DIR__ . '/.config.ini',
		SECRET_CONFIGURATION = __DIR__ . '/.secrets.ini',
		HASHIDS_CONFIGURATION = __DIR__ . '/.hashids.json',
		HASHIDS_SECRET_CONFIGURATION = __DIR__ . '/.hashids.secret.json',
		CACHE_KEY = 'combined_configs';

	public function read(): array {
		return (new Configuration\CachedSource(
			new Configuration\CombinedSource(
				new Configuration\ValidIni(new \SplFileInfo(self::CONFIGURATION)),
				new Configuration\ValidIni(new \SplFileInfo(self::SECRET_CONFIGURATION)),
				new Configuration\NamedSource(
					'HASHIDS',
					new CreatedHashids(
						new Configuration\CombinedSource(
							new Configuration\ValidJson(new \SplFileInfo(self::HASHIDS_CONFIGURATION)),
							new Configuration\ValidJson(new \SplFileInfo(self::HASHIDS_SECRET_CONFIGURATION))
						)
					)
				)
			),
			self::CACHE_KEY
		))->read();
	}
}